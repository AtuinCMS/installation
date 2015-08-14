<?php

namespace atuin\installation\app_installation;


use atuin\apps\models\App;
use atuin\apps\models\ModelAppConnections;
use atuin\config\models\Config;
use atuin\config\models\ModelConfig;
use atuin\engine\helpers\Filters;
use yii;


class AppManagement extends BaseManagement
{

    /** @var  App */
    protected $app_record;

    /**
     * @inheritdoc
     */
    function initialize($module, $type = 'install')
    {
        $this->defineModuleOperation($type);

        $this->module = $module;

        if (is_null($this->module))
        {
            throw new yii\base\InvalidParamException('Module is not loaded.');
        }

        if (!is_subclass_of($this->module, $this->parentClass))
        {
            throw new yii\base\InvalidParamException('Module must inherit from ' . $this->parentClass);
        }
    }


    /**
     * @return mixed
     */
    public function execute()
    {
        // adds the module to the modules database
        $moduleMethod = $this->preMethod . 'Module';

        return $this->$moduleMethod();
    }

    /**
     * @return App
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function upModule()
    {
        // check if module is already installed
        if (!is_null(App::findOne(['name' => $this->module->id, 'className' => $this->module->getModuleClassName()])))
        {
            throw new yii\base\Exception('Module is already installed');
        }

        // 1 - Adds the Module into the database
        $app_record = new App();
        $app_record->name = $this->module->getName();
        $app_record->className = $this->module->getModuleClassName();
        $app_record->directory = $this->module->getModuleDirectory();
        $app_record->namespace = $this->module->getModuleNamespace();
        $app_record->alias = '@' . $this->module->getAlias();
        $app_record->install_date = date("Y-m-d H:i:s");
        $app_record->backend = $this->module->is_backend;
        $app_record->frontend = $this->module->is_frontend;
        $app_record->core_module = $this->module->is_core_module;
        $app_record->status = 'active';
        $app_record->insert();

        
        // Add the class and aliases to each module config
        $this->normalizeModuleConfig($app_record);

        return $app_record;
    }

    /**
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function downModule()
    {
        /** @var App $app_record */
        $app_record = App::findOne(['name' => $this->module->id, 'className' => $this->module->getModuleClassName()]);
        if (!is_null($app_record))
        {
            if ($app_record->core_module == 1)
            {
                throw new yii\base\Exception('Module ' . $this->module->id . ' is core, so it can\'t be uninstalled.');
            }

            $app_record->delete();
            $app_record = NULL;
        } else
        {
            throw new yii\base\Exception('No installed APP named ' . $this->module->id . ' found.');
        }
    }

    /**
     * Adds the basic module data configuration
     *
     * @param App $app_record
     */
    protected function normalizeModuleConfig($app_record)
    {
        $appConnectionsModel = new ModelAppConnections();

        Filters::registerAction([Config::className(), Config::EVENT_AFTER_INSERT], [$appConnectionsModel, 'insertConnectionFromFilter'], $app_record);
        
        // Adds the class to the module config
        ModelConfig::addConfig(NULL, 'modules', $app_record->name, 'class', $app_record->className, FALSE);
        
        // Adds the alias to the module config
        ModelConfig::addConfig(NULL, 'aliases', NULL, $app_record->alias, $app_record->directory, FALSE);

        Filters::unRegisterAction([Config::className(), Config::EVENT_AFTER_INSERT]);
    }

}