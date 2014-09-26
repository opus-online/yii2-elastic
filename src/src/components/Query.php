<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 2.09.2014
 */

namespace opus\elastic\components;

use yii\base\InvalidParamException;
use yii\helpers\Json;

/**
 * Class Query
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\components
 */
class Query extends \yii\elasticsearch\Query
{
    /**
     * Creates multi search request to elasticsearch
     * @param array $requests
     * @param null $searchType
     * @param array $options
     * @throws \yii\base\InvalidParamException
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

        return \Yii::$app->elasticsearch->get('_msearch', $options, $query);
    }
}