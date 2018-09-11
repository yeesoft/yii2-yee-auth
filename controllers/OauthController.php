<?php

namespace yeesoft\auth\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\authclient\AuthAction;
use yii\web\NotFoundHttpException;
use yii\base\InvalidConfigException;
use yeesoft\models\User;
use yeesoft\auth\AuthModule;
use yeesoft\auth\models\Auth;
use yeesoft\widgets\ActiveForm;
use yeesoft\controllers\BaseController;
use yeesoft\auth\models\forms\SetEmailForm;
use yeesoft\auth\models\forms\SetPasswordForm;
use yeesoft\auth\models\forms\SetUsernameForm;
use yeesoft\auth\parsers\UserAttributesParser;

class OauthController extends BaseController
{

    /**
     * @var array
     */
    public $freeAccessActions = [];

    /**
     *
     * @var array 
     */
    private $_oauthActions = ['index', 'set-email', 'set-username', 'set-password'];

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
                    'access' => [
                        'class' => AccessControl::className(),
                        'only' => ['set-email', 'set-username', 'set-password'],
                        'rules' => [
                                [
                                'actions' => ['set-email', 'set-username', 'set-password'],
                                'allow' => true,
                                'roles' => ['?'],
                            ],
                        ],
                    ],
        ]);
    }

    public function init()
    {
        if ($this->module->enableOAuth) {
            $this->freeAccessActions = ArrayHelper::merge($this->freeAccessActions, $this->_oauthActions);
        }

        parent::init();
    }

    /**
     * @return array
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
                    'index' => [
                        'class' => AuthAction::class,
                        'successCallback' => [$this, 'afterAuthorization'],
                    ],
        ]);
    }

    public function afterAuthorization($client)
    {
        $source = $client->getId();
        $userAttributes = $client->getUserAttributes();

        if (!isset($this->module->attributeParsers[$source])) {
            throw new InvalidConfigException("There are no settings for '{$source}' in the AuthModule::attributeParsers.");
        }

        $attributeParserClass = $this->module->attributeParsers[$source];
        if (!class_exists($attributeParserClass)) {
            throw new InvalidConfigException("Class {$attributeParserClass} does not exist.");
        }

        /* @var $attributeParser UserAttributesParser */
        $attributeParser = new $attributeParserClass;

        if (!($attributeParser instanceof UserAttributesParser)) {
            throw new InvalidConfigException("Class {$attributeParserClass} must be an instance of " . UserAttributesParser::class . ".");
        }

        $attributes = $attributeParser->getAttributes($userAttributes);
        Yii::$app->session->set(AuthModule::USER_ATTRIBUTES_SESSION_KEY, $attributes);

        /* @var $auth Auth */
        $auth = Auth::find()->where([
                    'source' => $attributes['source'],
                    'source_id' => $attributes['source_id'],
                ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // signup
                if (isset($attributes['email']) && $attributes['email'] && User::find()->where(['email' => $attributes['email']])->exists()) {
                    Yii::$app->session->setFlash('error', Yii::t('yee/auth', 'An account with the same email address already exists. Sign in using your login or a provider associated with this email address.'));
                    $this->redirect(['/auth/default/login']);
                } else {

                    return $this->createUser($attributes);
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $attributes['source'],
                    'source_id' => $attributes['source_id'],
                ]);
                $auth->save();
            }
        }
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSetEmail()
    {
        return $this->performAction('email', 'set-email', SetEmailForm::class);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSetUsername()
    {
        return $this->performAction('username', 'set-username', SetUsernameForm::class);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSetPassword()
    {
        return $this->performAction('password', 'set-password', SetPasswordForm::class);
    }

    private function performAction($field, $view, $formClass)
    {
        $attributes = Yii::$app->session->get(AuthModule::USER_ATTRIBUTES_SESSION_KEY);

        if (!is_array($attributes) || !isset($attributes['source']) || !isset($attributes['source_id'])) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $model = new $formClass();

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            $attributes[$field] = $model->$field;
            Yii::$app->session->set(AuthModule::USER_ATTRIBUTES_SESSION_KEY, $attributes);
            return $this->createUser($attributes);
        }

        return $this->renderIsAjax($view, compact('model'));
    }

    private function createUser($attributes)
    {
        $auth = [
            'source' => (string) $attributes['source'],
            'source_id' => (string) $attributes['source_id'],
        ];

        unset($attributes['source']);
        unset($attributes['source_id']);

        $attributes['repeat_password'] = isset($attributes['password']) ? $attributes['password'] : null;

        $user = new User($attributes);

        $user->setScenario(User::SCENARIO_NEW_USER);
        $user->generateAuthKey();

        $transaction = $user->getDb()->beginTransaction();

        if ($user->save()) {
            $auth = new Auth([
                'user_id' => $user->id,
                'source' => $auth['source'],
                'source_id' => $auth['source_id'],
            ]);

            if ($auth->save()) {
                $transaction->commit();
                Yii::$app->user->login($user);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('yee', "Error {code}", ['code' => 901]) . ': ' . Yii::t('yee/auth', "Authentication error."));
                return $this->redirect(['/auth/default/login']);
            }
        } else {

            $errors = $user->getErrors();
            $fields = ['username', 'email', 'password'];

            foreach ($fields as $field) {
                if (isset($errors[$field])) {
                    Yii::$app->session->setFlash('error', $user->getFirstError($field));
                    return $this->redirect(['/auth/oauth/set-' . $field]);
                }
            }

            Yii::$app->session->setFlash('error', Yii::t('yee', "Error {code}", ['code' => 902]) . ': ' . Yii::t('yee/auth', "Authentication error."));
            return $this->redirect(['/auth/default/login']);
        }

        Yii::$app->session->remove(AuthModule::USER_ATTRIBUTES_SESSION_KEY);
        return $this->goHome();
    }

}
