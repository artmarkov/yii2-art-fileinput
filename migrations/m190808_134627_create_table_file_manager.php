<?php

use yii\db\Migration;

class m190808_134627_create_table_file_manager extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%file_manager}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'orig_name' => $this->string()->notNull(),
            'class' => $this->string(),
            'item_id' => $this->integer(),
            'alt' => $this->string(),
            'sort' => $this->smallInteger()->notNull(),
            'type' => $this->string()->notNull(),
            'filetype' => $this->string(),
            'size' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('created_by', '{{%file_manager}}', 'created_by');
        $this->addForeignKey('file_manager_ibfk_1', '{{%file_manager}}', 'created_by', '{{%user}}', 'id', 'RESTRICT', 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%file_manager}}');
    }
}
