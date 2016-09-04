<?php

use yii\db\Migration;

class m160903_113810_update_auth_foreign_key extends Migration
{

    const TABLE_NAME = '{{%auth}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->dropForeignKey('fk_auth_user', self::TABLE_NAME);
        $this->addForeignKey('fk_auth_user', self::TABLE_NAME, 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_auth_user', self::TABLE_NAME);
    }

}
