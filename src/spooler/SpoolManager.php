<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 28.08.2014
 */

namespace opus\elastic\spooler;

use yii\base\Object;

/**
 * Class ElasticManager
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic
 */
class SpoolManager extends Object
{
    /**
     * DataProvider classes which hold info for inserting data to elastic
     *
     * @var AbstractDataProvider[]
     */
    public $dataProviders = [];

    /**
     * Used to generate correct mapping for attributes
     *
     * @var array
     */
    public $languages = [];

    /**
     * Spooling data batch size
     *
     * @var int
     */
    public $batchSize = 100;

    /**
     * 1) check if index exists, if exists delete and create new one
     * 2) get mapping from provider and insert it
     * 3) import all products (w/o spool table)
     */
    public function reindex()
    {
        $this->createIndexes();
        $this->reindexData();
    }

    /**
     * Spools data to elastic index
     */
    public function spool()
    {
        foreach ($this->dataProviders as $dataProvider) {
            $this->spoolData($dataProvider);
        }
    }

    /**
     * Creates indexes
     *
     * @param bool $deleteOld deletes indexes if exists
     * @internal param array $indexes
     */
    private function createIndexes($deleteOld = true)
    {
        foreach ($this->dataProviders as $dataProvider) {
            $exists = \Yii::$app->elasticsearch->createCommand()->indexExists(
                $dataProvider->getIndexName()
            );
            if ($exists === true && $deleteOld === true) {
                \Yii::$app->elasticsearch->createCommand()->deleteIndex(
                    $dataProvider->getIndexName()
                );
            }
            \Yii::$app->elasticsearch->createCommand()->createIndex(
                $dataProvider->getIndexName()
            );
        }
    }

    /**
     * Creates mapping and insets data to elastic
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function reindexData()
    {
        foreach ($this->dataProviders as $dataProvider) {
            \Yii::$app->elasticsearch->createCommand()->setMapping(
                $dataProvider->getIndexName(),
                $dataProvider->getTypeName(),
                $dataProvider->getMapping($this->languages)
            );

            Spooler::reindexData(
                $dataProvider->getRecordClassName(),
                $dataProvider->getRecordClassTableName()
            );
            $this->spoolData($dataProvider);
        }
    }

    /**
     * Mass inserts data to elasticsearch
     *
     * @param AbstractDataProvider $dataProvider
     */
    private function spoolData(AbstractDataProvider $dataProvider)
    {
        $dataProvider->initializeDependentData();
        $offset = 0;
        while (Spooler::setProcessingRows(
                $this->batchSize,
                $offset,
                $dataProvider->getRecordClassName()
            ) > 0) {
            $data = $dataProvider->getData($this->languages);
            \Yii::$app->elasticsearch->createCommand()->bulk(
                $dataProvider->getIndexName(),
                $dataProvider->getTypeName(),
                $data
            );
            Spooler::deleteProcessingRows();
        }
    }
}

