<?php
namespace atuin\installation\app_installation;


use atuin\apps\models\App;
use atuin\apps\models\AppConnections;
use atuin\apps\models\ModelAppConnections;
use atuin\config\models\Config;
use atuin\config\models\ModelConfig;
use atuin\engine\helpers\Filters;
use atuin\installation\app_installation\helpers\ConfigFilesManager;
use atuin\installation\app_installation\helpers\FactoryCommandHelper;
use atuin\menus\model\ModelMenus;
use atuin\menus\models\ModelMenuItem;
use cyneek\yii2\menu\models\MenuItems;
use League\Flysystem\Adapter\Local as Adapter;
use League\Flysystem\Filesystem;
use yii;


class AppConfigManagement extends BaseManagement
{


    protected $migrationsDir = 'migrations';

    protected $installationObject;


    /** @var  App */
    protected $app;

    /**
     * Defines all the methods that will be called in the config class
     * Also defines the valid objects that this methods will have.
     *
     * @var array
     */
    public $methodTypes = [
        'Composer' => ['param' => '', 'class' => ''],
        'Migration' => [
            'param' => 'migration',
            'class' => 'atuin\installation\app_installation\helpers\ParamMigration'
        ],
        'Config' => [
            'param' => 'configItems',
            'class' => 'atuin\config\models\ModelConfig'
        ],
        'Menu' => [
            'param' => 'menuItems',
            'class' => 'cyneek\yii2\menu\models\MenuItems'
        ],
        'Manual' => ['param' => '', 'class' => '']
    ];

    /** @var array */
    protected $arrRoutes = ['backend', 'frontend'];


    /**
     * @inheritdoc
     */
    function initialize($module, $type = 'install')
    {
        parent::initialize($module, $type);

        $this->installationObject = $this->module->getConfigLibrary();


        if (is_null($this->installationObject)) {
            throw new yii\base\InvalidParamException('Config class for module ' . $this->module->id . ' is not defined.');
        }


        // Each method from config class has it's special parameter that can only be used within it,
        // to prevent using this parameters in the rest of the methods we will add an exception to each one

        foreach ($this->methodTypes as $method => $object_launched) {
            if ($object_launched['param']) {
                $this->createInstallException($object_launched['param']);
            }
        }

        if (!is_null(Yii::$app->db->schema->getTableSchema(App::tableName(), TRUE))) {
            /** @var App $app */
            $this->app = App::findOne(['name' => $this->module->id]);
        }
    }


    /**
     * Redeclares certain parameter from the config class into an exception to prevent the system from using it
     * in illegal methods
     *
     * @param string $parameter
     */
    protected function createInstallException($parameter)
    {
        $this->installationObject->$parameter = new yii\base\Exception('The object' . $parameter . 'it\'s not valid in this method.');
    }


    /**
     * Executes an installation or uninstallation, depending of the class launched
     */
    public function execute()
    {
        // changes the file locations for routes and so on...
        $filesMethod = $this->preMethod . 'Routes';
        $this->$filesMethod();

        // launches every defined method in the config class
        foreach ($this->methodTypes as $key => $item) {
            $this->launch($key);
        }

        $this->createConfigFiles();

    }

    /**
     * Creates the Config files each time an App Config installation is launched
     */
    protected function createConfigFiles()
    {
        // First check if we can launch the config files creation
        if (!is_null(Yii::$app->db->schema->getTableSchema(Config::tableName(), TRUE))
            && !is_null(Yii::$app->db->schema->getTableSchema(AppConnections::tableName(), TRUE))
        ) {
            ConfigFilesManager::generateConfigFiles();
        }
    }


    /**
     * Launches every defined method in the config class as UP or DOWN depending the
     * action we are taking with the app.
     *
     * @param $type
     */
    protected function launch($type)
    {
        $object_launched = $this->methodTypes[$type];

        if ($object_launched['param']) {
            if (class_exists($object_launched['class'])) {
                $this->installationObject->$object_launched['param'] = new $object_launched['class']();
            } else {
                return;
            }
        }

        call_user_func([$this, 'execute' . $type]);

        if ($object_launched['param']) {
            $this->createInstallException($object_launched['param']);
        }
    }

    protected function upRoutes()
    {
        // TODO integrate fully to flysystem

        $basePath = dirname(Yii::$app->getVendorPath());

        $filesystem = new Filesystem(new Adapter($basePath));

        // 1 - Move the routes into the route system in case we have those files
        $fileHelper = new yii\helpers\FileHelper();

        foreach ($this->arrRoutes as $type) {
            $path = $this->module->basePath . '/routes/' . $type . '/';

            if (is_dir($path)) {
                $files = $fileHelper->findFiles($path, ['only' => ['*.php'], 'recursive' => FALSE]);
                foreach ($files as $file) {
                    $fileName = str_replace($path, '', $file);
                    $from = str_replace($basePath, '', $file);

                    $to = '/atuin/routes/' . $type . '/app_' . $this->module->id . '_' . $fileName;

                    if (!$filesystem->has($to)) {
                        $filesystem->copy($from, $to);
                    }
                }
            }
        }
    }

    protected function downRoutes()
    {
        // TODO integrate fully to flysystem

        $fileHelper = new yii\helpers\FileHelper();
        $basePath = dirname(Yii::$app->getVendorPath());

        $filesystem = new Filesystem(new Adapter($basePath));

        foreach ($this->arrRoutes as $type) {

            $files = $fileHelper->findFiles($basePath . '/atuin/routes/' . $type, ['only' => ['app_' . $this->module->id . '*.php', ''], 'recursive' => FALSE]);
            foreach ($files as $file) {
                $delete_file = str_replace($basePath, '', $file);
                $filesystem->delete($delete_file);
            }
        }
    }


    protected function executeMigration()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // First we will try to search for migration files at the Apps' migration subdirectory.
            $baseDirectoryList = [];
            $baseDirectoryList[] = $this->module->getModuleDirectory();

            if (method_exists($this->module, 'getComposerPackageData')) {
                $baseDirectoryList[] = Yii::$app->getVendorPath() . '/' . $this->module->getComposerPackageData(TRUE);
            }

            foreach ($baseDirectoryList as $baseDirectory) {
                $filesystem = new Filesystem(new Adapter($baseDirectory));

                if ($filesystem->has($this->module->getMigrationsDirectory()) && FactoryCommandHelper::migration()->check()) {
                    FactoryCommandHelper::migration()->execute($baseDirectory . '/' . $this->module->getMigrationsDirectory(), $this->preMethod);
                }
            }


            // calling the method
            // since we are calling yii2 Migration AND has a lot of echos, let's capture the string output
            ob_start();
            $execution = call_user_func([$this->installationObject, $this->preMethod . 'Migration']);

            $outputText = ob_get_contents();

            if ($execution === FALSE) {
                $transaction->rollBack();

                return FALSE;
            }
            $transaction->commit();
            ob_end_clean();

        } catch (\Exception $e) {
            $transaction->rollBack();
            ob_end_clean();

            throw new yii\base\Exception($e->getMessage());
        }

    }

    /**
     * Executes the composer callings that the App may have in case it is a
     * composer module.
     *
     * If so, it will install all the packages that holds in the getComposerPackageData method.
     *
     */
    protected function executeComposer()
    {
        if (method_exists($this->module, 'getComposerPackageData') && FactoryCommandHelper::composer()->check()) {
            $package = $this->module->getComposerPackageData();

            if (is_null($package)) {
                return;
            } elseif (!is_array($package)) {
                $package = [$package];
            }

            foreach ($package as $p) {
                FactoryCommandHelper::composer()->execute($p, $this->preMethod);
            }

            // Now we will load the brand new aliases into the system to be able to use
            // the new packages installed in this system.

            $file = Yii::$app->getVendorPath() . '/yiisoft/extensions.php';
            if (!is_file($file)) {
                return [];
            }
            // invalidate opcache of extensions.php if exists
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, TRUE);
            }
            $extensions = require($file);

            foreach ($package as $p) {
                $p = explode(':', $p)[0];

                if (array_key_exists($p, $extensions)) {
                    $aliasList = $extensions[$p]['alias'];
                    Yii::$app->setAliases($aliasList);
                }
            }

        }
    }


    protected function executeMenu()
    {

        $appConnectionsModel = new ModelAppConnections();

        Filters::registerAction([MenuItems::className(), Config::EVENT_AFTER_INSERT], [$appConnectionsModel, 'insertConnectionFromFilter'], $this->app);

        // Deleting manually the already assigned menus
        if ($this->preMethod == 'down') {
            $modelMenus = new ModelMenuItem();
            $modelMenus->deleteAppMenuItems($this->app->id);
        }

        call_user_func([$this->installationObject, $this->preMethod . 'Menu']);

        Filters::unRegisterAction([Config::className(), Config::EVENT_AFTER_INSERT]);
    }


    protected function executeConfig()
    {

        $appConnectionsModel = new ModelAppConnections();

        Filters::registerAction([Config::className(), Config::EVENT_AFTER_INSERT], [$appConnectionsModel, 'insertConnectionFromFilter'], $this->app);

        // Deleting manually the already assigned configs
        if ($this->preMethod == 'down') {
            $modelConfig = new ModelConfig();
            $modelConfig->deleteAppConfigItems($this->app->id);
        }

        call_user_func([$this->installationObject, $this->preMethod . 'Config']);

        Filters::unRegisterAction([Config::className(), Config::EVENT_AFTER_INSERT]);

    }

    protected function executeManual()
    {
        return call_user_func([$this->installationObject, $this->preMethod . 'Manual']);
    }

}