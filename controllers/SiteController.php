<?php
namespace atuin\installation\controllers;

use amnah\yii2\user\models\User;
use atuin\installation\libraries\InstallationManager;
use atuin\installation\models\ModelInstallation;
use Yii;
use yii\base\Model;
use yii\web\Controller;


/**
 * Class InstallationController
 * @package atuin\engine\controllers\backend
 */
class SiteController extends Controller
{
    /** @inheritdoc */
    public $layout = 'installation_layout';

    public function actionDatabase()
    {

        $model = new ModelInstallation();

        $model->setScenario('database_installation');

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->databaseInstallation();

            return $this->refresh();
        }

        return $this->render('database_installation', ['model' => $model, 'form_title' => 'Database configuration', 'page_info' => '']);
    }


    public function actionAppbox()
    {
        $executeInstallation = (Yii::$app->request->post('type') === 'launch');

        $installationManager = new InstallationManager();

        $appInstallationResponse = $installationManager->checkAppInstallation($executeInstallation);


        if ($executeInstallation === FALSE)
        {
            if ($appInstallationResponse === FALSE)
            {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return FALSE;
            }

            return $this->renderAjax('app_installation_box', [
                    'title' => $appInstallationResponse->getData()
                ]
            );
        } else
        {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return $appInstallationResponse->toArray();
        }
    }

    public function actionAppinstallation()
    {

        return $this->render('app_installation', [
            'form_title' => 'Installing Atuin configuration apps', 'page_info' => '']);
    }

    public function actionUser()
    {

        $model = new ModelInstallation();
        $model_user = new User();

        $model_user->setScenario('reset');

        $model->setScenario('username_installation');


        if ($model->load(Yii::$app->request->post()) && $model_user->load(Yii::$app->request->post())
            && Model::validateMultiple([$model, $model_user])
        )
        {

            $model->titleInstallation();

            $role = Yii::$app->getModule("user")->model("Role");

            $model_user->role_id = $role::ROLE_ADMIN;

            $model_user->status = User::STATUS_ACTIVE;
            $model_user->create_time = date('Y-m-d H:i:s');
            $model_user->create_ip = Yii::$app->request->getUserHost();
            $model_user->api_key = Yii::$app->security->generateRandomString();
            $model_user->auth_key = Yii::$app->security->generateRandomString();

            $model_user->save();

            $profile_data = Yii::$app->getModule("user")->model("Profile");
            $profile_data->user_id = $model_user->id;
            $profile_data->full_name = $model_user->username;
            $profile_data->create_time = date('Y-m-d H:i:s');

            $profile_data->save(FALSE);


            return $this->refresh();
        }

        return $this->render('username_installation', ['model' => $model, 'model_user' => $model_user,
            'form_title' => 'System configuration', 'page_info' => '']);
    }


}
