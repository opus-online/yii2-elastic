<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 22.08.2014
 */

namespace opus\elastic\spooler;

use yii\base\ErrorException;
use yii\console\Controller;

/**
 * Class ElasticController
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\spooler
 */
class ElasticController extends Controller
{
    /**
     * Data providers to index/spool
     * @var string[]
     */
    public $dataProviders = [];
    /**
     * @throws \yii\base\ErrorException
     */
    public function actionImport()
    {
        $elasticManager = new SpoolManager(
            [
                'dataProviders' => $this->createDataProviders($this->dataProviders)
            ]
        );
        $elasticManager->reindex();
    }

    /**
     * @throws \yii\base\ErrorException
     */
    public function actionSpool()
    {
        $elasticManager = new SpoolManager(
            [
                'dataProviders' => $this->createDataProviders($this->dataProviders)
            ]
        );
        $elasticManager->spool();
    }

    /**
     * @param AbstractDataProvider[] $dataProviders
     * @throws ErrorException
     * @return array
     */
    private function createDataProviders(array $dataProviders)
    {
        $dataProviderModels = [];

        foreach ($dataProviders as $dataProvider) {
            $dataProvider = new $dataProvider();
            if (!($dataProvider instanceof AbstractDataProvider)) {
                throw new ErrorException(
                    'dataProvider is not instance of DataProviderInterface'
                );
            }
            $dataProviderModels[] = $dataProvider;
        }
        return $dataProviderModels;
    }
}
