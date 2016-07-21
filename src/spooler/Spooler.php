<?php
/**
 * Created by PhpStorm.
 * User: Mihkel
 * Date: 27.08.14
 * Time: 22:34
 */

namespace opus\elastic\spooler;

use yii\base\Object;
use yii\db\Exception;
use yii\db\Query;

/**
 * This class operates with spool table
 * Mass inserts, individual record save, marking processing rows are supported
 * Class Spooler
 *
 * @package opus\elastic
 */
class Spooler extends Object
{
    public static $tableName = 'ym_elastic_spool_item';

    /**
     * @param $actionCode string ElasticSearch action code (INDEX, DELETE supported)
     * @param $modelName string ActiveRecord class name
     * @param $recordId int ActiveRecord record id
     * @return \yii\db\Command
     */
    public static function saveItem($actionCode, $modelName, $recordId)
    {
        return (new Query())
            ->createCommand()
            ->insert(
                \Yii::$app->elasticsearch->spoolerTableName,
                [
                    'action_code' => $actionCode,
                    'model_class' => $modelName,
                    'record_id' => $recordId,
                    'is_processing' => 0,
                ]
            )->execute();
    }

    /**
     *
     * @param $modelName string
     * @param $modelTable string
     * @throws Exception
     * @throws \Exception
     * @return \yii\db\Command
     */
    public static function reindexData($modelName, $modelTable)
    {
        $tableName = \Yii::$app->elasticsearch->spoolerTableName;
        $modelTable = sprintf('{{%s}}', $modelTable);
        $rows = 0;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            \Yii::$app->db->createCommand()->delete(
                self::$tableName,
                ['model_class' => $modelName]
            );

            $sql =
                "INSERT INTO $tableName
              ([[model_class]], [[record_id]], [[action_code]], [[is_processing]])
            SELECT :modelName, [[id]], 'INDEX', 0 FROM $modelTable";

            $rows = \Yii::$app->db
                ->createCommand($sql, [':modelName' => $modelName])
                ->execute();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $rows;
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    public static function deleteProcessingRows()
    {
        return \Yii::$app->db
            ->createCommand()
            ->delete(
                \Yii::$app->elasticsearch->spoolerTableName,
                ['is_processing' => 1]
            )
            ->execute();
    }

    /**
     * Marks a batch to is_processing
     *
     * @param integer $limit
     * @param integer $offset
     * @param string $class
     * @return int
     */
    public static function setProcessingRows($limit, $offset, $class)
    {
        $tableName = \Yii::$app->elasticsearch->spoolerTableName;
        return \Yii::$app->db->createCommand(
            "UPDATE $tableName spool
            INNER JOIN (
                SELECT id FROM $tableName
                WHERE model_class = :class
                LIMIT $limit OFFSET $offset
            ) AS spool_join ON spool_join.id = spool.id
            SET spool.is_processing = 1",
            [':class' => $class]
        )->execute();
    }

    /**
     * Provides total count
     *
     * @param $className
     * @return int
     */
    public static function getTotalCount($className)
    {
        return (new Query())
            ->select('COUNT(id)')
            ->from(\Yii::$app->elasticsearch->spoolerTableName)
            ->where(['model_class' => $className])
            ->scalar();
    }

    /**
     * Sets all rows to is_processing to 0
     * @return int
     * @throws Exception
     */
    public static function removeProcessingRows()
    {
        $tableName = \Yii::$app->elasticsearch->spoolerTableName;
        return \Yii::$app->db->createCommand(
            "UPDATE $tableName spool SET spool.is_processing = 0"
        )->execute();
    }
}
