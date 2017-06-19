<?php

use choate\xsexpress\models\Producer;
use yii\db\Migration;

class m170615_091746_xsexpress_init extends Migration
{
    public function up()
    {
        $table = Producer::tableName();
        $this->createTable($table, [
            'uuid' => $this->string(36),
            'message' => $this->text(),
            'topic' => $this->string(50),
            'created_at' => $this->integer(11),
        ]);
        $this->addPrimaryKey('uuid_pk', $table, 'uuid');
        $this->createIndex('created_at_index', $table, 'created_at');
    }

    public function down()
    {
        echo "m170615_091746_xsexpress_init cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
