<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 1.09.2014
 */

namespace opus\elastic\search;

use Elastica\Filter\Term;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchAll;
use opus\elastic\components\Query;
use opus\elastic\components\QueryBuilder;
use yii\base\BaseObject;
use yii\di\ServiceLocator;
use yii\elasticsearch\ActiveQuery;
use yii\elasticsearch\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractQueryProvider
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\search
 */
abstract class AbstractQueryProvider extends BaseObject
{
    /**
     * Initial request params, that contains all the parameters in filter/query part
     * @var mixed[]
     */
    protected $requestParams = [];

    /**
     * Query instance
     * @var Query
     */
    protected $query;

    /**
     * QueryBuilder instance to handle yii default syntax eg ['not' => ['id' => 'test']]
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * This service locator holds all attribute handlers
     * @var ServiceLocator
     */
    protected $locator;

    /**
     * Class instance for formatting results after successful request to elasticsearch
     * @var string
     */
    public $resultsFormatter;

    /**
     * Return the searchable data model
     *
     * @return ActiveRecord
     */
    abstract public function getModel();

    /**
     * Returns query handlers for special cases
     * @return array
     */
    abstract public function attributeHandlers();

    /**
     * @param array $requestParams
     * @param array $config
     */
    public function __construct($requestParams = [], $config = [])
    {
        parent::__construct($config);

        $this->queryBuilder = \Yii::createObject(QueryBuilder::class, [null]);

        $this->locator = new ServiceLocator();
        $this->locator->setComponents($this->attributeHandlers());

        $query = $this->getQueryInstance();
        $query->query = new BoolQuery();
        $query->filter = new \opus\elastic\elastica\filter\BoolQuery();
        $query->limit = ArrayHelper::remove($requestParams, 'limit', 0);
        $query->offset = ArrayHelper::remove($requestParams, 'offset', 0);
        $query->orderBy = ArrayHelper::remove($requestParams, 'sort');

        $this->requestParams = $requestParams;

        $this->setAttributes();
    }
    /**
     * This method is used to add conditions to query
     *
     * @param string $attribute
     * @param string|array $value
     * @return $this
     */
    public function setAttribute($attribute, $value)
    {
        $query = $this->getQueryInstance();
        if ($this->locator->has($attribute)) {
            /** @var QueryHandlerInterface $specialHandler */
            $specialHandler = $this->locator->get($attribute);
            list($query->query, $query->filter, $query->aggregations) = $specialHandler->handle([
                'query' => $query->query,
                'filter' => $query->filter,
                'aggregations' => $query->aggregations,
                'value' => $value
            ]);
        } elseif ($this->isValidAttribute($attribute)) {
            $filter = new Term(
                [$attribute => $value]);
            $query->filter->addMust($filter);
        }
        return $this;
    }

    /**
     * Avoids sending junk to search server
     * @param $attribute
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function isValidAttribute($attribute)
    {
        return in_array($attribute, $this->getModel()->attributes());
    }

    /**
     * Mass sets attributes to query
     */
    protected function setAttributes()
    {
        foreach ($this->requestParams as $field => $condition) {
            if (is_numeric($field) && is_array($condition)) {
                $builtCondition = $this->queryBuilder->buildCondition($condition);
                $this->getQueryInstance()->filter->addMust([$builtCondition]);
            } else {
                $this->setAttribute($field, $condition);
            }
        }
    }

    /**
     * @param bool $multiSearch
     * @return ActiveQuery|array
     */
    public function getQuery($multiSearch = true)
    {
        $query = $this->getQueryInstance();
        $activeQuery = $this->getBaseQuery();

        if ($multiSearch === true) {
            $activeQuery = [
                'query' => [
                    'bool' => [
                        'must' => $activeQuery->createCommand()->queryParts['query'],
                        'filter' => isset($activeQuery->createCommand()->queryParts['filter']) ?: null
                    ],
                ],
                'from' => $query->offset,
                'size' => $query->limit,
            ];
            if (!is_null($query->orderBy)) {
                $activeQuery['sort'] = $this->getQueryInstance()->orderBy;
            }
            if (!empty($query->aggregations)) {
                $activeQuery['aggs'] = $this->getQueryInstance()->aggregations;
            }
            return $activeQuery;
        }

        return $activeQuery
            ->limit($query->limit)
            ->offset($query->offset)
            ->orderBy($query->orderBy);
    }

    /**
     * Returns base query
     * @return ActiveQuery
     */
    private function getBaseQuery()
    {
        $query = $this->getQueryInstance();

        $query->query = $query->query == new BoolQuery()
            ? new MatchAll() : $query->query;

        $query->filter = $query->filter == new \Elastica\Query\BoolQuery
            ? new \Elastica\Filter\MatchAll() : $query->filter;

        return $this->getModel()
                    ->find()
                    ->query($query->query)
                    ->where($query->filter)
                    ->limit(null)
                    ->offset(null)
                    ->orderBy(null);

    }

    /**
     * @param array $aggregations
     * @return AbstractQueryProvider
     */
    public function setAggregations($aggregations)
    {
        $this->query->aggregations = $aggregations;
        return $this;
    }

    /**
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     * @return ResultsFormatterInterface
     */
    public function getResultsFormatter($config = [])
    {
        $config = ArrayHelper::merge($config, [
            'queryProvider' => $this,
            'class' => $this->resultsFormatter
        ]);
        return \Yii::createObject($config);
    }

    /**
     * Returns user search keywords
     * @return array
     */
    public function getSearchKeywords()
    {
        return $this->requestParams;
    }

    /**
     * @return Query
     */
    public function getQueryInstance()
    {
        if (is_null($this->query)) {
            $this->query = \Yii::createObject(Query::class);
        }
        return $this->query;
    }
}
