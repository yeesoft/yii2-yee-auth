<?php

namespace yeesoft\auth\controllers;

use Yii;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yeesoft\models\User;
use yeesoft\widgets\ActiveForm;
use yeesoft\auth\AuthEvent;
use yeesoft\controllers\BaseController;
use yeesoft\auth\models\forms\LoginForm;
use yeesoft\auth\models\forms\SignupForm;
use yeesoft\auth\models\forms\ResetPasswordForm;
use yeesoft\auth\models\forms\UpdatePasswordForm;

class DefaultController extends BaseController
{

    /**
     * @var array
     */
    public $freeAccessActions = ['login', 'logout', 'captcha'];

    /**
     *
     * @var array 
     */
    private $_registrationActions = ['signup', 'confirm-email'];

    /**
     *
     * @var array 
     */
    private $_resetPasswordActions = ['password-reset'];

    public function init()
    {
        if ($this->module->enableRegistration) {
            $this->freeAccessActions = ArrayHelper::merge($this->freeAccessActions, $this->_registrationActions);
        }

        if ($this->module->enablePasswordReset) {
            $this->freeAccessActions = ArrayHelper::merge($this->freeAccessActions, $this->_resetPasswordActions);
        }

        parent::init();
    }

    /**
     * @return array
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
                    'captcha' => Yii::$app->captchaAction,
        ]);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
                    'access' => [
                        'class' => AccessControl::className(),
                        'only' => ['logout', 'signup', 'confirm-email', 'password-reset'],
                        'rules' => [
                                [
                                'actions' => ['signup', 'confirm-email', 'password-reset'],
                                'allow' => true,
                                'roles' => ['?'],
                            ],
                                [
                                'actions' => ['logout'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'logout' => ['post'],
                        ],
                    ],
        ]);
    }

    /**
     * Logout and redirect to home page
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Login form
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->login()) {
            return $this->goBack();
        }

        return $this->renderIsAjax('login', compact('model'));
    }

    /**
     * Registration page
     *
     * @return string
     */
    public function actionSignup()
    {
        $model = new SignupForm();

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $model->validate();
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {

            if (Yii::$app->controller->module->termsAndConditions && !$model->terms) {
                Yii::$app->session->setFlash('warning', Yii::t('yee/auth', 'You must agree to the terms and conditions before registering.'));
                return $this->renderIsAjax('signup', compact('model'));
            }

            if ($this->triggerModuleEvent(AuthEvent::BEFORE_REGISTRATION, ['model' => $model])) {
                $user = $model->signup(false);

                if ($user && $this->triggerModuleEvent(AuthEvent::AFTER_REGISTRATION, ['model' => $model, 'user' => $user])) {
                    if (Yii::$app->controller->module->enableEmailConfirmation) {
                        Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'Check your email {email} for instructions to activate account.', ['email' => '<b>' . $user->email . '</b>']));
                        return $this->redirect(['login']);
                    } else {
                        $user->assignRoles(Yii::$app->defaultRoles);
                        Yii::$app->user->login($user);
                        return $this->redirect(Yii::$app->user->returnUrl);
                    }
                }
            }
        }

        return $this->renderIsAjax('signup', compact('model'));
    }

    /**
     * Receive token after registration, find user by it and confirm email.
     *
     * @param string $token
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string|\yii\web\Response
     */
    public function actionConfirmEmail($token)
    {
        if (Yii::$app->controller->module->enableEmailConfirmation) {
            $expired = $this->isTokenExpired($token);
            $user = User::findOne(['confirmation_token' => $token, 'status' => User::STATUS_INACTIVE]);

            if (!$expired && $user) {
                $user->status = User::STATUS_ACTIVE;
                $user->email_confirmed = 1;
                $user->removeConfirmationToken();
                $user->save(false);
                $user->assignRoles(Yii::$app->defaultRoles);
                Yii::$app->user->login($user);

                Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'You have successfully confirmed your account.'));
                return $this->goHome();
            }

            Yii::$app->session->setFlash('danger', Yii::t('yee/auth', 'Token not found. It may be expired.'));
        }

        return $this->redirect(['login']);
    }

    /**
     * Action to reset password
     *
     * @return string|\yii\web\Response
     */
    public function actionPasswordReset($token = null)
    {
        if ($token) {
            $expired = $this->isTokenExpired($token);
            $user = User::findOne(['confirmation_token' => $token, 'status' => User::STATUS_ACTIVE]);

            if ($expired || !$user) {
                Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'Token not found. It may be expired. Please try to reset your password again.'));
                return $this->redirect(['password-reset']);
            }

            $model = new UpdatePasswordForm(['scenario' => UpdatePasswordForm::SCENARIO_EMAIL_RESET, 'user' => $user]);

            if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                if ($this->triggerModuleEvent(AuthEvent::BEFORE_PASSWORD_RECOVERY_COMPLETE, ['model' => $model])) {
                    $model->updatePassword(false);

                    if ($this->triggerModuleEvent(AuthEvent::AFTER_PASSWORD_RECOVERY_COMPLETE, ['model' => $model])) {
                        Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'You have successfully updated the password.'));
                        return $this->redirect(['login']);
                    }
                }
            }

            return $this->renderIsAjax('password-update', compact('model'));
        } else {
            $model = new ResetPasswordForm();

            if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->validate();
            }

            if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                if ($this->triggerModuleEvent(AuthEvent::BEFORE_PASSWORD_RECOVERY_REQUEST, ['model' => $model])) {
                    if ($model->sendEmail(false)) {
                        if ($this->triggerModuleEvent(AuthEvent::AFTER_PASSWORD_RECOVERY_REQUEST, ['model' => $model])) {
                            Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'Check your email for a link to reset your password.'));
                            return $this->redirect(['login']);
                        }
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('yee/auth', 'Unable to send a message to provided email address. Please try again.'));
                    }
                }
            }

            return $this->renderIsAjax('password-reset', compact('model'));
        }
    }

    /**
     * Method for triggering events like "before registration", "after registration" and so on
     *
     * @param string $eventName
     * @param array $data
     *
     * @return bool
     */
    protected function triggerModuleEvent($eventName, $data = [])
    {
        $event = new AuthEvent($data);

        Yii::$app->trigger($eventName, $event);

        return $event->isValid;
    }

    private function isTokenExpired($token)
    {
        $lifetime = Yii::$app->controller->module->confirmationTokenLifetime;

        $parts = explode('_', $token);
        $timestamp = (int) end($parts);

        return ($timestamp + $lifetime < time());
    }

}