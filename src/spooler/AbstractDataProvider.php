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
     * Languages
     * @var array
     */
    public $languages = [];

    /**
     * @var string[][] Elastic index mapping
     */
    private $mapping = [];

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
     * @return array
     */
    public function getMapping()
    {
        if (empty($this->mapping)) {
            /** @var AbstractMappingProvider $mappingProvider */
            $mappingProvider = \Yii::createObject('opus\elastic\spooler\AbstractMappingProvider');
            $attributes = $mappingProvider->build($this->languages);

            $this->mapping = [
                $this->getTypeName() => $attributes
            ];
        }
        return $this->mapping;

    }

    /**
     * Callback to be used to init dependant data
     * @return mixed
     */
    abstract public function initializeDependentData();
}
