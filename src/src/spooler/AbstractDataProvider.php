<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 22.08.2014
 */

namespace opus\elastic\spooler;

use yii\base\Object;

/**
 * Interface DataProviderAbstract
 *
 * @package opus\elastic
 */
abstract class AbstractDataProvider extends Object
{
    /**
     * Provides data to spool into elastic
     *
     * @return array
     */
    abstract function getData();

    /**
     * Gives ES type name where the data should be inserted to
     * @return string
     */
    abstract function getTypeName();

    /**
     * Gives ES index name where the data should be inserted to
     * @return string
     */
    abstract function getIndexName();

    /**
     * Returns ActiveRecord class name
     * @return string
     */
    abstract function getRecordClassName();

    /**
     * Returns ActiveRecord table name
     * @return string
     */
    abstract function getRecordClassTableName();

    /**
     * @param array $languages
     * @return array
     */
    public function getMapping(array $languages)
    {
        /** @var AbstractMappingProvider $mappingProvider */
        $mappingProvider = \Yii::createObject('opus\elastic\spooler\AbstractMappingProvider');
        $attributes = $mappingProvider->build($languages);

        return [
            $this->getTypeName() => $attributes
        ];
    }

    /**
     * Callback to be used to init dependant data
     * @return mixed
     */
    abstract public function initializeDependentData();
}
