<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 2.09.2014
 */

namespace opus\elastic\search;

use opus\elastic\components\Query;
use yii\base\ErrorException;
use yii\base\BaseObject;

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
class SearchHandler extends BaseObject
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
     * @return mixed[]
     */
    public function search()
    {
        $preparedQueries = $this->prepareQueryProviders();
        $result = (new Query())->multiSearch($preparedQueries);
        return $result;
    }

    /**
     * Returns total count of results (all query responses total)
     * @return int
     */
    public function getCount()
    {
        $preparedQueries = $this->prepareQueryProviders();
        $result = (new Query())->multiSearch($preparedQueries);
        $count = 0;
        foreach ($result['responses'] as $response) {

            $count += $response['hits']['total'];
        }
        return $count;
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
}
