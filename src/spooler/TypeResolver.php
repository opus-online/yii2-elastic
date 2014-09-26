<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 25.08.2014
 */

namespace opus\elastic\spooler;

use yii\base\ErrorException;

/**
 * Class TypeResolver
 * This class resolves elasticsearch type configuration
 * If more types are needed into configuration then you can override path param
 * with yii container parameter setter
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic
 */
class TypeResolver
{
    /**
     * Path to the types config file
     * @var string
     */
    public $path = '@root/ext//yii2-elastic/src/config/types';

    /**
     * Cached configuration
     * @var array
     */
    private $config = [];

    /**
     * Loads configuration
     */
    function __construct()
    {
        if (empty($this->config)) {
            $this->config = require(\Yii::getAlias($this->path) . '.php');
        }
    }

    /**
     * @param $type
     * @throws \yii\base\ErrorException
     * @return
     */
    public function resolve($type)
    {
        if (!isset($this->config[$type])) {
            throw new ErrorException('Unknown type:' . $type );
        }
        return $this->config[$type];
    }
} 