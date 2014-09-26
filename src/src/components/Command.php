<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 25.08.2014
 */

namespace opus\elastic\components;
use yii\base\ErrorException;
use yii\caching\Cache;
use yii\helpers\Json;

/**
 * Class Command
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package common\components\elasticsearch
 */
class Command extends \yii\elasticsearch\Command
{
    /**
     * @param $index
     * @param $type
     * @param $data
     * @param array $options
     * @return mixed
     * @throws ErrorException
     */
    public function bulk($index, $type, $data, $options = [])
    {
        if (empty($data)) {
            $body = '{}';
        } else {
            $body = is_array($data) ? $this->formatBulkData($index, $type, $data) : $data;
        }
        return $this->db->post([$index, $type, '_bulk'], $options, $body);
    }

    /**
     * @param $index
     * @param $type
     * @param $data
     * @return string
     * @throws ErrorException
     */
    private function formatBulkData($index, $type, $data)
    {
        $formattedData = [];

        foreach ($data as $operation => $objects) {
            foreach ($objects as $object) {
                switch ($operation) {
                    case 'INDEX' :
                        $formattedData[] = Json::encode(
                            [
                                'index' => [
                                    '_index' => $index,
                                    '_type' => $type,
                                    '_id' => $object['id']
                                ]
                            ]
                        );
                        $formattedData[] = Json::encode($object);
                        break;

                    case 'DELETE' :
                        $formattedData[] = Json::encode(
                            [
                                'delete' => [
                                    '_index' => $index,
                                    '_type' => $type,
                                    '_id' => $object['id']
                                ]
                            ]
                        );
                        break;
                    default:
                        throw new ErrorException(
                            'Unknown operation ' . $operation
                        );
                }
            }
        };
        return implode("\n", $formattedData) . "\n";
    }

    /**
     * getMapping with caching support
     * @param string $index
     * @param string $type
     * @return array
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-get-mapping.html
     */
    public function getCachedMapping($index = '_all', $type = '_all')
    {
        $command = \Yii::$app->elasticsearch;
        /* @var $cache Cache */
        $cache = is_string($command->cache) ? \Yii::$app->get($command->cache, false) : $command->cache;
        if ($cache instanceof Cache) {
            $key = sprintf('%s_%s', $index, $type);
            if (($mapping = $cache->get($key)) === false) {
                $mapping = $this->getMapping($index, $type);
                if ($mapping !== null) {
                    $cache->set($key, $mapping);
                }
            }
        }
        else {
            $mapping = $this->getMapping($index, $type);
        }
        return $mapping;
    }

    /**
     * Overridden to return exact type mapping if possible
     * @param string $index
     * @param string $type
     * @return array
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-get-mapping.html
     */
    public function getMapping($index = '_all', $type = '_all')
    {
        $mapping = parent::getMapping($index, $type);
        if ($index !== '_all' && $type !== '_all') {
            $mapping = $mapping[$index]['mappings'][$type]['properties'];
        }
        return $mapping;
    }
}
