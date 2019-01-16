<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 2.09.2014
 */

namespace opus\elastic\components;

use yii\base\InvalidParamException;
use yii\elasticsearch\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Query
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\components
 * @property \opus\elastic\elastica\filter\BoolQuery $filter
 */
class Query extends \yii\elasticsearch\Query
{
    /**
     * @var array|string|\opus\elastic\elastica\filter\BoolQuery The filter part of this search query. This is an array or json string that follows the format of
     * the elasticsearch [Query DSL](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl.html).
     */
    public $filter;

    /**
     * @var array|string|\Elastica\Query\Bool The query part of this search query. This is an array or json string that follows the format of
     * the elasticsearch [Query DSL](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl.html).
     */
    public $query;

    /**
     * Creates multi search request to elasticsearch
     *
     * @param array $requests
     * @param null $searchType
     * @param array $options
     * @throws Exception
     * @return mixed
     */
    public static function multiSearch(
        array $requests,
        $searchType = null,
        $options = []
    ) {
        if (empty($requests)) {
            throw new InvalidParamException('Cannot create empty request');
        }
        $requestParts = [];
        foreach ($requests as $request) {
            $requestParts[] = Json::encode(
                [
                    'index' => $request['index'],
                    'type' => $request['type'],
                    'search_type' => $searchType ?: 'query_then_fetch'
                ]
            );
            $requestParts[] = Json::encode($request['query']);
        }
        $query = implode("\n", $requestParts) . "\n";

        $result = \Yii::$app->elasticsearch->get('_msearch', $options, $query);

        foreach ($result['responses'] as $set) {
            if (($error = ArrayHelper::getValue($set, 'error')) !== null) {
                throw new Exception($error);
            }
        }

        return $result;
    }
}