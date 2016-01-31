<?php
/**
 * @link http://www.yee-soft.com/
 * @copyright Copyright (c) 2015 Taras Makitra
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace yeesoft\auth;

use Yii;
use yii\base\Exception;

/**
 * @author Taras Makitra <makitrataras@gmail.com>
 */
class AuthAction extends \yii\authclient\AuthAction
{

    /**
     * Runs the action.
     */
    public function run()
    {
        try {
            return parent::run();
        } catch (Exception $ex) {
            Yii::$app->session->setFlash('error', $ex->getMessage());
            //Yii::$app->session->setFlash('error', Yii::t('yee/auth', "Authentication error occured."));

            return $this->redirectCancel();
        }
    }
}