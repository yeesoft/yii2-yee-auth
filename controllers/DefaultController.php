<?php

namespace yeesoft\auth\controllers;

use Yii;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yeesoft\auth\models\Auth;
use yeesoft\base\controllers\BaseController;
use yeesoft\usermanagement\UserManagementModule;
use yeesoft\usermanagement\components\UserAuthEvent;
use yeesoft\auth\models\forms\ChangeOwnPasswordForm;
use yeesoft\auth\models\forms\ConfirmEmailForm;
use yeesoft\auth\models\forms\LoginForm;
use yeesoft\auth\models\forms\PasswordRecoveryForm;
use yeesoft\auth\models\forms\RegistrationForm;
use yeesoft\usermanagement\models\User;

class DefaultController extends BaseController
{
    /**
     * @var array
     */
    public $freeAccessActions = ['login', 'logout', 'confirm-registration-email',
        'registration', 'password-recovery', 'captcha', 'oauth'];

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'captcha' => Yii::$app->getModule('user-management')->captchaOptions,
            'oauth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
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





        /* print_r($attributes);
          die; */

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
                    Yii::$app->getSession()->setFlash('error',
                        [
                        Yii::t('app',
                            "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.",
                            ['client' => $client->getTitle()]),
                    ]);
                    Yii::$app->getResponse()->redirect(['auth/default/login']);
                } else {
                    $password    = Yii::$app->security->generateRandomString(6);
                    $user        = new User([
                        'username' => ArrayHelper::getValue($attributes,
                            $usernamePath),
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
                            'source_id' => (string) $attributes['id'],
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
     * Change your own password
     *
     * @throws \yii\web\ForbiddenHttpException
     * @return string|\yii\web\Response
     */
    public function actionChangeOwnPassword()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $user = User::getCurrentUser();

        if ($user->status != User::STATUS_ACTIVE) {
            throw new ForbiddenHttpException();
        }

        $model = new ChangeOwnPasswordForm(['user' => $user]);


        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->changePassword()) {
            return $this->renderIsAjax('changeOwnPasswordSuccess');
        }

        return $this->renderIsAjax('changeOwnPassword', compact('model'));
    }

    /**
     * Registration logic
     *
     * @return string
     */
    public function actionRegistration()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new RegistrationForm;


        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            // Ajax validation breaks captcha. See https://github.com/yiisoft/yii2/issues/6115
            // Thanks to TomskDiver
            $validateAttributes = $model->attributes;
            unset($validateAttributes['captcha']);

            return ActiveForm::validate($model, $validateAttributes);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            // Trigger event "before registration" and checks if it's valid
            if ($this->triggerModuleEvent(UserAuthEvent::BEFORE_REGISTRATION,
                    ['model' => $model])) {
                $user = $model->registerUser(false);

                // Trigger event "after registration" and checks if it's valid
                if ($this->triggerModuleEvent(UserAuthEvent::AFTER_REGISTRATION,
                        ['model' => $model, 'user' => $user])) {
                    if ($user) {
                        if (Yii::$app->getModule('user-management')->useEmailAsLogin AND Yii::$app->getModule('user-management')->emailConfirmationRequired) {
                            return $this->renderIsAjax('registrationWaitForEmailConfirmation',
                                    compact('user'));
                        } else {
                            $roles = (array) Yii::$app->getModule('user-management')->rolesAfterRegistration;

                            foreach ($roles as $role) {
                                User::assignRole($user->id, $role);
                            }

                            Yii::$app->user->login($user);

                            return $this->redirect(Yii::$app->user->returnUrl);
                        }
                    }
                }
            }
        }

        return $this->renderIsAjax('registration', compact('model'));
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
        if (Yii::$app->getModule('user-management')->useEmailAsLogin AND Yii::$app->getModule('user-management')->emailConfirmationRequired) {
            $registrationFormClass = Yii::$app->getModule('user-management')->registrationFormClass;
            $model                 = new $registrationFormClass;

            $user = $model->checkConfirmationToken($token);

            if ($user) {
                return $this->renderIsAjax('confirmEmailSuccess',
                        compact('user'));
            }

            throw new NotFoundHttpException(UserManagementModule::t('front',
                'Token not found. It may be expired'));
        }
    }

    /**
     * Form to recover password
     *
     * @return string|\yii\web\Response
     */
    public function actionPasswordRecovery()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new PasswordRecoveryForm();

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Ajax validation breaks captcha. See https://github.com/yiisoft/yii2/issues/6115
            // Thanks to TomskDiver
            $validateAttributes = $model->attributes;
            unset($validateAttributes['captcha']);

            return ActiveForm::validate($model, $validateAttributes);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            if ($this->triggerModuleEvent(UserAuthEvent::BEFORE_PASSWORD_RECOVERY_REQUEST,
                    ['model' => $model])) {
                if ($model->sendEmail(false)) {
                    if ($this->triggerModuleEvent(UserAuthEvent::AFTER_PASSWORD_RECOVERY_REQUEST,
                            ['model' => $model])) {
                        return $this->renderIsAjax('passwordRecoverySuccess');
                    }
                } else {
                    Yii::$app->session->setFlash('error',
                        UserManagementModule::t('front',
                            "Unable to send message for email provided"));
                }
            }
        }

        return $this->renderIsAjax('passwordRecovery', compact('model'));
    }

    /**
     * Receive token, find user by it and show form to change password
     *
     * @param string $token
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string|\yii\web\Response
     */
    public function actionPasswordRecoveryReceive($token)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $user = User::findByConfirmationToken($token);

        if (!$user) {
            throw new NotFoundHttpException(UserManagementModule::t('front',
                'Token not found. It may be expired. Try reset password once more'));
        }

        $model = new ChangeOwnPasswordForm([
            'scenario' => 'restoreViaEmail',
            'user' => $user,
        ]);

        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
            if ($this->triggerModuleEvent(UserAuthEvent::BEFORE_PASSWORD_RECOVERY_COMPLETE,
                    ['model' => $model])) {
                $model->changePassword(false);

                if ($this->triggerModuleEvent(UserAuthEvent::AFTER_PASSWORD_RECOVERY_COMPLETE,
                        ['model' => $model])) {
                    return $this->renderIsAjax('changeOwnPasswordSuccess');
                }
            }
        }

        return $this->renderIsAjax('changeOwnPassword', compact('model'));
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
            if ($this->triggerModuleEvent(UserAuthEvent::BEFORE_EMAIL_CONFIRMATION_REQUEST,
                    ['model' => $model])) {
                if ($model->sendEmail(false)) {
                    if ($this->triggerModuleEvent(UserAuthEvent::AFTER_EMAIL_CONFIRMATION_REQUEST,
                            ['model' => $model])) {
                        return $this->refresh();
                    }
                } else {
                    Yii::$app->session->setFlash('error',
                        UserManagementModule::t('front',
                            "Unable to send message for email provided"));
                }
            }
        }

        return $this->renderIsAjax('confirmEmail', compact('model'));
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
            throw new NotFoundHttpException(UserManagementModule::t('front',
                'Token not found. It may be expired'));
        }

        $user->email_confirmed = 1;
        $user->removeConfirmationToken();
        $user->save(false);

        return $this->renderIsAjax('confirmEmailSuccess', compact('user'));
    }

    /**
     * Universal method for triggering events like "before registration", "after registration" and so on
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return bool
     */
    protected function triggerModuleEvent($eventName, $data = [])
    {
        $event = new UserAuthEvent($data);

        Yii::$app->getModule('user-management')->trigger($eventName, $event);

        return $event->isValid;
    }
}