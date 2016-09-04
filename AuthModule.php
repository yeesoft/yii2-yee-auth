<?php

/**
 * @link http://www.yee-soft.com/
 * @copyright Copyright (c) 2015 Taras Makitra
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace yeesoft\auth;

use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yeesoft\models\User;

/**
 * Auth Module For Yee CMS
 *
 * @author Taras Makitra <makitrataras@gmail.com>
 */
class AuthModule extends \yii\base\Module
{

    /**
     * Version number of the module.
     */
    const VERSION = '0.1.0';
    const PARAMS_SESSION_ID = 'authUserParams';

    /**
     * Bootstrap grid columns count.
     *
     * @var int
     */
    public $gridColumns = 12;

    /**
     * Profile layout.
     *
     * @var string
     */
    public $profileLayout;

    /**
     * List of functions for parsing user auth data. This list will be merged with
     * parsers from `AuthModule::getDefaultAttributeParsers()`. You can overwrite
     * default parsers.
     *
     * @var array
     */
    public $attributeParsers;

    /**
     * Controller namespace
     *
     * @var string
     */
    public $controllerNamespace = 'yeesoft\auth\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->attributeParsers === null) {
            $this->attributeParsers = [];
        }
        
        $this->attributeParsers = ArrayHelper::merge(self::getDefaultAttributeParsers(), $this->attributeParsers) ;
    }

    public static function getDefaultAttributeParsers()
    {
        return [
            'google' => function($attributes) {
                $result['source'] = 'google';
                $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
                $result['email'] = ArrayHelper::getValue($attributes, 'emails.0.value');
                $username = ArrayHelper::getValue($attributes, 'displayName');
                $result['username'] = Inflector::slug($username, '_');
                $result['first_name'] = ArrayHelper::getValue($attributes, 'name.givenName');
                $result['last_name'] = ArrayHelper::getValue($attributes, 'name.familyName');
                return $result;
            },
            'facebook' => function($attributes) {
                $result['source'] = 'facebook';
                $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
                $result['email'] = ArrayHelper::getValue($attributes, 'email');
                $username = ArrayHelper::getValue($attributes, 'name');
                $result['username'] = Inflector::slug($username, '_');
                return $result;
            },
            'twitter' => function($attributes) {
                $result['source'] = 'twitter';
                $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
                $result['email'] = null;
                $username = ArrayHelper::getValue($attributes, 'screen_name');
                $result['username'] = Inflector::slug($username, '_');
                $result['first_name'] = ArrayHelper::getValue($attributes, 'name');
                return $result;
            },
            'github' => function($attributes) {
                $result['source'] = 'github';
                $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
                $result['email'] = ArrayHelper::getValue($attributes, 'email');
                $username = ArrayHelper::getValue($attributes, 'name');
                $result['username'] = Inflector::slug($username, '_');
                return $result;
            },
            'linkedin' => function($attributes) {
                $result['source'] = 'linkedin';
                $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
                $result['email'] = ArrayHelper::getValue($attributes, 'email');
                $username = ArrayHelper::getValue($attributes, 'first-name');
                $result['username'] = Inflector::slug($username, '_');
                return $result;
            },
            'vkontakte' => function($attributes) {
                $result['source'] = 'vkontakte';
                $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
                $result['email'] = ArrayHelper::getValue($attributes, 'email');
                $result['first_name'] = ArrayHelper::getValue($attributes, 'first_name');
                $result['last_name'] = ArrayHelper::getValue($attributes, 'last_name');
                
                $username = $result['first_name'] . ' ' . $result['last_name'];
                $result['username'] = Inflector::slug($username, '_');
                
                $gender = ArrayHelper::getValue($attributes, 'sex');
                if($gender == 2){
                    $result['gender'] = User::GENDER_MALE;
                } elseif($gender == 1){
                    $result['gender'] = User::GENDER_FEMALE;
                } else {
                    $result['gender'] = User::GENDER_NOT_SET;
                }
                
                $birthday = ArrayHelper::getValue($attributes, 'bdate');
                if ($birthday) {
                    $values = explode('.', $birthday);
                    $result['birth_day'] = isset($values[0]) ? $values[0] : null;
                    $result['birth_month'] = isset($values[1]) ? $values[1] : null;
                    $result['birth_year'] = isset($values[2]) ? $values[2] : null;
                }
                
                return $result;
            },
            
        ];
    }

}
