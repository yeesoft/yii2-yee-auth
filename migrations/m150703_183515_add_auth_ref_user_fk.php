<?php

use yii\db\Migration;
use yii\db\Schema;

class m150703_183515_add_auth_ref_user_fk extends Migration
{

    public function up()
    {
        $this->addForeignKey(
            'fk_auth_user',
            'auth',
            'user_id',
            'user',
            'id',
            'RESTRICT',
            'RESTRICT'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_auth_user', 'auth');
    }
}
