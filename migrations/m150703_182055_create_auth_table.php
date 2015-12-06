<?php

use yii\db\Migration;
use yii\db\Schema;

class m150703_182055_create_auth_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('auth', [
            'id' => 'pk',
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'source' => Schema::TYPE_STRING . '(255) NOT NULL',
            'source_id' => Schema::TYPE_STRING . '(255) NOT NULL',
        ], $tableOptions);

        $this->addForeignKey('fk_auth_user', 'auth', 'user_id', 'user', 'id', 'RESTRICT', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_auth_user', 'auth');
        $this->dropTable('auth');
    }
}