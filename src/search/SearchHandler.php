<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 2.09.2014
 */

namespace opus\elastic\search;

use opus\elastic\components\Query;
use yii\base\ErrorException;
use yii\base\Object;
use yii\elasticsearch\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class SearchHandler
 *
 * This class operates with single|multiple query providers
 * to send multiple search queries in one request
 * After the request it wraps all results to AbstractHit
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\search
 */
class SearchHandler extends Object
{
    /**
     * @var AbstractQueryProvider[]
     */
    public $queryProviders = [];

    /**
     * @param array $queries
     * @param array $config
     */
    public function __construct(array $queries, $config = [])
    {
        parent::__construct($config);
        $this->queryProviders = $queries;
    }

    /**
     * Prepares query provider data, makes search and formats results into AbstractHit objects
     *
     * @throws ErrorException
     * @return AbstractResultWidget[]
     */
    public function search()
    {
        $preparedQueries = $this->prepareQueryProviders();
        $result = (new Query())->multiSearch($preparedQueries);
        return $this->formatResultSets($result['responses']);
    }

    /**
     * Returns total count of results (all query responses total)
     * @return int
     */
    public function getCount()
    {
        $preparedQueries = $this->prepareQueryProviders();
        $result = (new Query())->multiSearch($preparedQueries, 'count');
        $count = 0;
        foreach ($result['responses'] as $response) {

            $count += $response['hits']['total'];
        }
        return $count;
    }

    /**
     * @throws Exception
     * @return mixed
     */
    public function getAggregations()
    {
        $preparedQueries = $this->prepareQueryProviders();
        $result = (new Query())->multiSearch($preparedQueries);
        $aggregations = [];
        foreach ($result['responses'] as $response) {
            if (isset($response['error'])) {
                throw new Exception($response['error']);
            }
            $aggregations = ArrayHelper::merge($response['aggregations'], $aggregations);
        }
        return $aggregations;
    }

    /**
     * Returns formatted requests for elasticSearch multi query method
     *
     * @return array
     */
    private function prepareQueryProviders()
    {
        $requests = [];
        foreach ($this->queryProviders as $provider) {
            $requests[] = [
                'index' => $provider->getModel()->index(),
                'type' => $provider->getModel()->type(),
                'query' => $provider->getQuery(),
            ];
        }
        return $requests;
    }

    /**
     * Formats all results to AbstractResultWidget object
     *
     * @param array $resultSets
     * @return AbstractResultWidget[]
     */
    private function formatResultSets(array $resultSets)
    {
        $hits = [];
        $counter = 0;
        foreach ($resultSets as $set) {
            foreach ($set['hits']['hits'] as $hit) {
                $provider = $this->resolveResultProvider($hit);
                $hitModel = $provider->getResultInstance();
                $hitModel
                    ->setSource($hit['_source'])
                    ->setResultId($provider->offset + $counter);

                $hits[] = $hitModel;
                $counter++;
            }
            $counter = 0;
        }
        return $hits;
    }

    /**
     * Resolves hit model by index and type match
     *
     * @param array $hit
     * @throws ErrorException
     * @return AbstractQueryProvider
     */
    private function resolveResultProvider(array $hit)
    {
        foreach ($this->queryProviders as $provider) {
            $sameIndex = $provider->getModel()->index() === $hit['_index'];
            $sameType = $provider->getModel()->type() === $hit['_type'];

            if ($sameIndex === true && $sameType === true) {
                return $provider;
            }
        }
        throw new ErrorException('Could not find provider');
    }
}
