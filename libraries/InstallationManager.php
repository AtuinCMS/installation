<?php
namespace atuin\installation\libraries;

use amnah\yii2\user\models\User;
use atuin\apps\models\App;
use atuin\engine\helpers\AjaxResponse;
use atuin\installation\app_installation\AppConfigManagement;
use atuin\installation\app_installation\AppInstaller;
use atuin\installation\app_installation\AppLoader;
use atuin\installation\app_installation\AppManagement;
use atuin\installation\helpers\ComposerAppHandler;
use atuin\installation\helpers\FileSystem;
use Yii;
use yii\base\ErrorException;
use yii\base\Object;

/**
 * Class SystemInstallation
 *
 * Does the heavy lifting of the Atuin CMS system installation.
 *
 * @package atuin\installation\libraries
 */
class InstallationManager extends Object
{

    protected $coreModules = [
        'config', 'menus', 'apps', 'installation'
    ];

    protected $basicModules = [
        'atuin-user' => [
            'id' => 'atuin-user',
            'composerPackage' => 'atuin/atuin-user:dev-master',
            'namespace' => 'atuin\user'
        ],
        'atuin-routes' => [
            'composerPackage' => 'atuin/atuin-routes:dev-master',
            'namespace' => 'atuin\routes'
        ]
    ];

    /**
     * Executes the installation manager
     */
    public function execute()
    {
        /** @var \atuin\installation\Module $moduleLink */
        $moduleLink = \Yii::$app->getModule('installation');


        if ($this->checkInstallation()) {
            throw new ErrorException('Atuin System is already installed. The SystemInstallacion Class should be only
            instantiated to install the Atuin System.');
        }


        // STEP 1 : CHECK IF MAIN ATUIN SUBDIRECTORY IT'S WRITABLE

        if (!is_writable($moduleLink->getInstallationDirectory())) {
            throw new ErrorException('The Atuin base directory it\'s not writable.');
        }


        // STEP 2 : CHECK IF CONFIG AND APPS SUBDIRECTORIES EXIST, IF NOT, CREATE THEM

        $directories = $moduleLink->getSubdirectories();

        foreach ($directories as $dir_key => $directory) {
            if (!FileSystem::fileSystem()->has($directory)) {
                FileSystem::fileSystem()->createDir($directory);
            }
        }

        // STEP 3 : INITIALIZE DATABASE CONFIGURATION WITH THE FORM INPUT

        if ($this->checkDBInstallation() === FALSE) {
            return;
        }

        // STEP 4 : INSTALLING THE BASIC CONFIG AND CORE APPS 

        if ($this->checkAppInstallation() !== TRUE) {
            return;
        }


        // STEP 5 : INITIALIZE USER AND TITLE CONFIGURATION WITH THE FORM INPUT

        if ($this->checkUserInstallation() === FALSE) {
            return;
        }

        // STEP 6 : CREATE INSTALLED FILE

        FileSystem::createFile('installation.php', '1');

        // STEP 7 : RELOAD PAGE TO GET NEW CONFIG FILES

        Yii::$app->getResponse()->redirect('@atuin/loginUrl');
    }

    /**
     * Checks if the Atuin CMS system it's fully installed
     *
     * @return bool
     */
    public static function checkInstallation()
    {
        return FileSystem::fileSystem()->has('installation.php');
    }


    /**
     * Checks if the Database Config of Atuin is already installed and, if not
     * makes the arrangements to do it.
     *
     * @return bool
     */
    private function checkDBInstallation()
    {
        $configPath = \Yii::$app->getModule('installation')->getSubdirectories('config');

        if (!Filesystem::fileSystem()->has($configPath . '/config-db.php')) {
            // Set this action controller as unique on the system.
            // This way we enforce the system DB configuration
            Yii::$app->catchAll = ['installation/site/database'];
            Yii::$app->urlManager->enablePrettyUrl = TRUE;

            return FALSE;
        }

        return TRUE;
    }

    private function checkUserInstallation()
    {

        if (User::find()->count() < 1) {
            // Set this action controller as unique on the system.
            // This way we enforce the system DB configuration
            Yii::$app->catchAll = ['installation/site/user'];
            Yii::$app->urlManager->enablePrettyUrl = TRUE;

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Checks the app installation, since it will be done one app each time
     * it will have to handle multiple tasks at once.
     *
     * -* Checks if every config and apps are installed
     *  * Checks if the config is installed and installs it
     *  * Checks if there is any app uninstalled and installs it
     *  * Everything depends of the request type.
     *
     * @param bool $executeInstallation
     * @return AjaxResponse|bool
     */
    public function checkAppInstallation($executeInstallation = FALSE)
    {


        // 1 - Checks the app installation states

        // Check if basic configs are installed
        $configInstalled = !is_null(Yii::$app->db->schema->getTableSchema(App::tableName(), TRUE));

        // Check if the core apps are installed

        $coreAppsInstalled = TRUE;

        if ($configInstalled === TRUE) {
            foreach ($this->basicModules as $id => $data) {
                if (is_null(App::findOne(['name' => $id]))) {
                    $coreApp = $id;
                    $coreAppsInstalled = FALSE;
                    break;
                }
            }
        }

        // If everything is installed, return TRUE and carry on.
        if ($configInstalled === TRUE and $coreAppsInstalled === TRUE) {
            if (Yii::$app->request->getIsAjax() === FALSE) {
                return TRUE;

            } else {
                Yii::$app->catchAll = ['installation/site/appbox'];
                Yii::$app->urlManager->enablePrettyUrl = TRUE;

                return FALSE;
            }
        }

        // If there is something without installation and the call is not
        // ajax then we will serve the main app installation page

        if (($configInstalled === FALSE or $coreAppsInstalled === FALSE)
            and Yii::$app->request->getIsAjax() === FALSE
        ) {
            Yii::$app->catchAll = ['installation/site/appinstallation'];
            Yii::$app->urlManager->enablePrettyUrl = TRUE;

            return FALSE;
        }


        //   $executeInstallation = (Yii::$app->request->post('type') === 'launch');

        $response = new AjaxResponse();

        // If the basic configs are not installed and an ajax call then:
        //      - if is launch type -> install them
        //      - if is not launch type -> return a box with the text

        if ($configInstalled === FALSE and Yii::$app->request->getIsAjax() === TRUE) {
            if ($executeInstallation === TRUE) {
                try {
                    $appLoader = new AppLoader();

                    // For the coreModules we will only apply the Configs
                    // because they are already installed via Composer
                    foreach ($this->coreModules as $coreModule) {
                        $app = $appLoader->loadApp($coreModule);
                        AppInstaller::execute($app, [new AppConfigManagement()]);
                    }
                } catch (\Exception  $e) {
                    $response->setErrorMessage($e->getMessage() . $e->getTraceAsString());
                }
                $response->setData(TRUE);
            } else {
                $response->setData('Installing basic configuration');
            }


        }

        if ($coreAppsInstalled === FALSE and Yii::$app->request->getIsAjax() === TRUE) {
            if ($executeInstallation === TRUE) {
                try {
                    $appLoader = new AppLoader();
                    // Now we will install the basic modules, like user, routes, etc...
                    // Using right now Composer only, in the future we will be able to use another 
                    // forms of installing apps.

                    $data = $this->basicModules[$coreApp];
                    $app = $appLoader->loadApp($coreApp, new ComposerAppHandler($data));
                    AppInstaller::execute($app, [new AppManagement(), new AppConfigManagement()]);
                } catch (\Exception $e) {
                    $response->setErrorMessage($e->getMessage());
                }
            } else {
                $response->setData('Installing core app ' . $coreApp);
            }

        }

        Yii::$app->catchAll = ['installation/site/appbox'];
        Yii::$app->urlManager->enablePrettyUrl = TRUE;

        return $response;
    }

}