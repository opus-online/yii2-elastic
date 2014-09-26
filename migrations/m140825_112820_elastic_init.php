<?php

use yii\db\Schema;
/**
 * @inheritdoc
 * @SuppressWarnings(ShortMethodName)
 * @SuppressWarnings(CamelCaseClassName)
 */
class m140825_112820_elastic_init extends \yii\db\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('ym_elastic_spool_item', [
            'id' => Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'model_class' => Schema::TYPE_STRING . '(256) NOT NULL',
            'record_id' => Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL',
            'action_code' => Schema::TYPE_STRING . '(32) NOT NULL',
            'is_processing' => 'tinyint(1) NOT NULL',
            'PRIMARY KEY (`id`)',
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('ym_elastic_spool_item');
    }
}
