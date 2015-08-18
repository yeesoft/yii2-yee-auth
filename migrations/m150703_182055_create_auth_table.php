<?php

use yii\db\Migration;
use yii\db\Schema;

class m150703_182050_create_auth_table extends Migration
{

    public function safeUp()
    {
        $this->createTable('auth', [
            'id' => 'pk',
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'source string' => Schema::TYPE_STRING . '(255) NOT NULL',
            'source_id' => Schema::TYPE_STRING . '(255) NOT NULL',
        ]);

        $this->addForeignKey('fk_auth_user', 'auth', 'user_id', 'user', 'id', 'RESTRICT', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_auth_user', 'auth');
        $this->dropTable('auth');
    }
}