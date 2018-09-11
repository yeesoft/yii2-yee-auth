<?php

namespace yeesoft\auth\controllers;

use yeesoft\auth\assets\AvatarAsset;
use yeesoft\auth\models\Auth;
use yeesoft\auth\models\forms\UpdatePasswordForm;
use yeesoft\controllers\BaseController;
use yeesoft\models\User;
use yeesoft\widgets\ActiveForm;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\imagine\Image as Imagine;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ProfileController extends BaseController
{

    /**
     * @var array
     */
    public $freeAccessActions = [];

    /**
     *
     * @var array 
     */
    private $_profileActions = ['index', 'upload-avatar', 'remove-avatar', 'update-password', 'unlink-client'];

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
                    'access' => [
                        'class' => AccessControl::className(),
                        'only' => ['index', 'upload-avatar', 'remove-avatar', 'update-password', 'unlink-client'],
                        'rules' => [
                                [
                                'actions' => ['index', 'upload-avatar', 'remove-avatar', 'update-password', 'unlink-client'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'upload-avatar' => ['post'],
                            'remove-avatar' => ['post'],
                        ],
                    ],
        ]);
    }

    public function init()
    {
        if ($this->module->enableProfile) {
            $this->freeAccessActions = ArrayHelper::merge($this->freeAccessActions, $this->_profileActions);
        }

        if ($this->module->profileLayout) {
            $this->layout = $this->module->profileLayout;
        }

        parent::init();
    }

    /**
     * @return string|Response
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        if ($user->load(Yii::$app->request->post()) AND $user->save()) {
            Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'Your profile has been updated.'));
        }

        return $this->renderIsAjax('profile', compact('user'));
    }

    /**
     * Change your own password
     *
     * @throws ForbiddenHttpException
     * @return string|Response
     */
    public function actionUpdatePassword()
    {
        $user = Yii::$app->user->identity;

        if ($user->status != User::STATUS_ACTIVE) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('danger', Yii::t('yee/auth', 'Your account is not active.'));
            return $this->redirect(['/auth/default/login']);
        }

        $model = new UpdatePasswordForm(compact('user'));

        if (Yii::$app->request->isAjax AND $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) AND $model->updatePassword()) {
            Yii::$app->session->setFlash('success', Yii::t('yee/auth', 'Password has been updated.'));
            return $this->redirect(['/auth/profile/update-password']);
        }

        return $this->renderIsAjax('update-password', compact('model'));
    }

    public function actionUploadAvatar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new DynamicModel(['image']);
        $model->addRule('image', 'file', ['skipOnEmpty' => false, 'extensions' => 'png, jpg']);

        if (Yii::$app->request->isPost) {
            $model->image = UploadedFile::getInstanceByName('image');

            if ($model->validate()) {
                try {
                    return self::uploadAvatar($model->image);
                } catch (Exception $exc) {
                    Yii::$app->response->statusCode = 400;
                    return Yii::t('yee', 'An unknown error occurred.');
                }
            } else {
                $errors = $model->getErrors();
                Yii::$app->response->statusCode = 400;
                return $model->getFirstError(key($errors));
            }
        }

        return;
    }

    public function actionRemoveAvatar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            Yii::$app->user->identity->removeAvatar();
            AvatarAsset::register($this->view);
            return AvatarAsset::getDefaultAvatar('large');
        } catch (Exception $exc) {
            Yii::$app->response->statusCode = 400;
            return 'Error occured!';
        }

        return;
    }

    public function actionUnlinkClient($redirectUrl = null)
    {
        $client = Yii::$app->getRequest()->get('authclient');
        if (!Auth::unlinkClient($client)) {
            Yii::$app->session->addFlash('error', Yii::t('yee/auth', 'Unable to unlink authorization client.'));
        }

        return $this->redirect($redirectUrl ? $redirectUrl : ['/auth/profile/index']);
    }

    /**
     *
     * @param UploadedFile $image
     * @return string
     */
    private static function uploadAvatar($image)
    {
        $uploadPath = 'uploads/avatar';
        $extension = '.' . $image->extension;
        $fileName = 'avatar_' . Yii::$app->user->identity->id . '_' . time();
        $sourceFile = $uploadPath . '/' . $fileName . $extension;

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $image->saveAs($sourceFile);

        Imagine::$driver = [Imagine::DRIVER_GD2, Imagine::DRIVER_GMAGICK, Imagine::DRIVER_IMAGICK];
        $sizes = [
            'small' => 48,
            'medium ' => 96,
            'large' => 144,
        ];

        foreach ($sizes as $alias => $size) {
            $avatarUrl = "$uploadPath/$fileName-{$size}x{$size}$extension";
            Imagine::thumbnail($sourceFile, $size, $size)->save($avatarUrl);
            $avatars[$alias] = "/$avatarUrl";
            Yii::$app->user->identity->setAvatars($avatars);
        }

        return $avatars;
    }

}
