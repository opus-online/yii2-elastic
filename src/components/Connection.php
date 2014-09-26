<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 25.08.2014
 */

namespace opus\elastic\components;

use yii\base\InvalidParamException;
use yii\caching\Cache;

/**
 * Class Connection
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package common\components\elasticsearch
 */
class Connection extends \yii\elasticsearch\Connection
{
    /**
     * @var Cache|string the cache object or the ID of the cache application component
     * that is used for query caching.
     */
    public $cache = 'cache';

    /**
     * Spooler table name
     * @var string
     */
    public $spoolerTableName = '{{ym_elastic_spool_item}}';

    /**
     * Elastic search index name
     * @var string
     */
    public $index;

    public function init()
    {
        parent::init();
        if (is_null($this->index)) {
            throw new InvalidParamException('Property "index" must be set');
        }
    }

    /**
     * Creates a command for execution.
     * @param array $config the configuration for the Command class
     * @return Command the DB command
     */
    public function createCommand($config = [])
    {
        $this->open();
        $config['db'] = $this;
        $command = new Command($config);

        return $command;
    }

    /**
     * Creates new query builder instance
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }
}