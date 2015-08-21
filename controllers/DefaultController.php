<?php

namespace yeesoft\auth\controllers;

use yeesoft\auth\models\Auth;
use yeesoft\auth\models\forms\ConfirmEmailForm;
use yeesoft\auth\models\forms\LoginForm;
use yeesoft\auth\models\forms\ResetPasswordyForm;
use yeesoft\auth\models\forms\SignupForm;
use yeesoft\auth\models\forms\UpdatePasswordForm;
use yeesoft\components\AuthEvent;
use yeesoft\controllers\BaseController;
use yeesoft\models\User;
use yeesoft\Yee;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class DefaultController extends BaseController
{
    /**
     * @var array
     */
    public $freeAccessActions = ['login', 'logout', 'captcha', 'oauth', 'signup',
        'confirm-email', 'confirm-registration-email', 'confirm-email-receive',
        'reset-password', 'reset-password-request', 'update-password'];

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'captcha' => Yii::$app->getModule('yee')->captchaOptions,
            'oauth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ]);
    }

    public function onAuthSuccess($client)
    {
        $values = [
            'google' => [
                'email' => 'emails.0.value',
                'username' => 'displayName',
            ],
            'facebook' => [
                'email' => 'email',
                'username' => 'name',
            ],
            'twitter' => [
                'username' => 'screen_name',
            ],
        ];

        $attributes = $client->getUserAttributes();
        $authClient = $client->getId();

        /* @var $auth Auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // signup
                $emailPath = ArrayHelper::getValue($values, "$authClient.email");
                $usernamePath = ArrayHelper::getValue($values, "$authClient.username");
                $email = ($emailPath) ? ArrayHelper::getValue($attributes, $emailPath) : '';

                if ($emailPath && $email && User::find()->where(['email' => $email])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $client->getTitle()]),
                    ]);
                    Yii::$app->getResponse()->redirect(['auth/default/login']);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => ArrayHelper::getValue($attributes, $usernamePath),
                        'email' => $email,
                        'password' => $password,
                    ]);
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $client->getId(),
                    'source_id' => $attributes['id'],
                ]);
                $auth->save();
            }
        }
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
     * Logout and redirect to home page
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(Yii::$app->homeUrl);
    }

    /**
     * Signup page
     *
     * @return string
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm;

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $model->validate();
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            // Trigger event "before registration" and checks if it's valid
            if ($this->triggerModuleEvent(AuthEvent::BEFORE_REGISTRATION, ['model' => $model])) {

                $user = $model->signup(false);

                // Trigger event "after registration" and checks if it's valid
                if ($user && $this->triggerModuleEvent(AuthEvent::AFTER_REGISTRATION, ['model' => $model, 'user' => $user])) {

                    if (Yii::$app->getModule('yee')->emailConfirmationRequired) {
                        return $this->renderIsAjax('signup-confirmation', compact('user'));
                    } else {
                        $user->assignRoles(Yii::$app->getModule('yee')->rolesAfterRegistration);

                        Yii::$app->user->login($user);

                        return $this->redirect(Yii::$app->user->returnUrl);
                    }
                }
            }
        }

        return $this->renderIsAjax('signup', compact('model'));
    }

    /**
     * Receive token after registration, find user by it and confirm email
     *
     * @param string $token
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string|\yii\web\Response
     */
    public function actionConfirmRegistrationEmail($token)
    {
        if (Yii::$app->getModule('yee')->emailConfirmationRequired) {

            $model = new SignupForm;

            $user = $model->checkConfirmationToken($token);

            if ($user) {
                return $this->renderIsAjax('confirm-email-success', compact('user'));
            }

            throw new NotFoundHttpException(Yee::t('front', 'Token not found. It may be expired'));
        }
    }

    /**
     * Change your own password
     *
     * @throws \yii\web\ForbiddenHttpException
     * @return string|\yii\web\Response
     */
    public function actionUpdatePassword()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $user = User::getCurrentUser();

        if ($user->status != User::STATUS_ACTIVE) {
            throw new ForbiddenHttpException();
        }

        $model = new UpdatePasswordForm(compact('user'));

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->updatePassword()) {
            return $this->renderIsAjax('update-password-success');
        }

        return $this->renderIsAjax('update-password', compact('model'));
    }

    /**
     * Action to reset password
     *
     * @return string|\yii\web\Response
     */
    public function actionResetPassword()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new ResetPasswordyForm();

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $model->validate();
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            if ($this->triggerModuleEvent(AuthEvent::BEFORE_PASSWORD_RECOVERY_REQUEST, ['model' => $model])) {
                if ($model->sendEmail(false)) {
                    if ($this->triggerModuleEvent(AuthEvent::AFTER_PASSWORD_RECOVERY_REQUEST, ['model' => $model])) {
                        return $this->renderIsAjax('reset-password-success');
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yee::t('front', "Unable to send message for email provided"));
                }
            }
        }

        return $this->renderIsAjax('reset-password', compact('model'));
    }

    /**
     * Receive token, find user by it and show form to change password
     *
     * @param string $token
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string|\yii\web\Response
     */
    public function actionResetPasswordRequest($token)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $user = User::findByConfirmationToken($token);

        if (!$user) {
            throw new NotFoundHttpException(Yee::t('front', 'Token not found. It may be expired. Try reset password once more'));
        }

        $model = new UpdatePasswordForm([
            'scenario' => 'restoreViaEmail',
            'user' => $user,
        ]);

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            if ($this->triggerModuleEvent(AuthEvent::BEFORE_PASSWORD_RECOVERY_COMPLETE, ['model' => $model])) {
                $model->updatePassword(false);

                if ($this->triggerModuleEvent(AuthEvent::AFTER_PASSWORD_RECOVERY_COMPLETE, ['model' => $model])) {
                    return $this->renderIsAjax('update-password-success');
                }
            }
        }

        return $this->renderIsAjax('update-password', compact('model'));
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionConfirmEmail()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $user = User::getCurrentUser();

        if ($user->email_confirmed == 1) {
            return $this->renderIsAjax('confirmEmailSuccess', compact('user'));
        }

        $model = new ConfirmEmailForm([
            'email' => $user->email,
            'user' => $user,
        ]);

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            if ($this->triggerModuleEvent(AuthEvent::BEFORE_EMAIL_CONFIRMATION_REQUEST, ['model' => $model])) {
                if ($model->sendEmail(false)) {
                    if ($this->triggerModuleEvent(AuthEvent::AFTER_EMAIL_CONFIRMATION_REQUEST, ['model' => $model])) {
                        return $this->refresh();
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yee::t('front', "Unable to send message for email provided"));
                }
            }
        }

        return $this->renderIsAjax('confirm-email', compact('model'));
    }

    /**
     * Receive token, find user by it and confirm email
     *
     * @param string $token
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string|\yii\web\Response
     */
    public function actionConfirmEmailReceive($token)
    {
        $user = User::findByConfirmationToken($token);

        if (!$user) {
            throw new NotFoundHttpException(Yee::t('front', 'Token not found. It may be expired'));
        }

        $user->email_confirmed = 1;
        $user->removeConfirmationToken();
        $user->save(false);

        return $this->renderIsAjax('confirm-email-success', compact('user'));
    }

    /**
     * Universal method for triggering events like "before registration", "after registration" and so on
     *
     * @param string $eventName
     * @param array $data
     *
     * @return bool
     */
    protected function triggerModuleEvent($eventName, $data = [])
    {
        $event = new AuthEvent($data);

        Yii::$app->getModule('yee')->trigger($eventName, $event);

        return $event->isValid;
    }
}