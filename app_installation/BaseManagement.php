<?php

namespace atuin\installation\app_installation;

use yii;

/**
 * Class BaseManagement
 * 
 * Abstract class used to make the App Installation class and App Config Management class t
 * 
 * @package atuin\installation\app\installation
 */
abstract class BaseManagement
{

    /** @var \atuin\skeleton\Module */
    public $module;

    /** @var string */
    public $parentClass = 'atuin\skeleton\Module';


    /** @var string */
    protected $preMethod;


    /**
     * Loads the module and checks if it's a valid Atuin Module
     *
     * @param \atuin\skeleton\Module $module
     * @param string $type
     */
    function initialize($module, $type = 'install')
    {
        $this->defineModuleOperation($type);

        $this->module = $module;

        if (is_null($this->module)) {
            throw new yii\base\InvalidParamException('Module is not loaded.');
        }

        if (!is_subclass_of($this->module, $this->parentClass)) {
            throw new yii\base\InvalidParamException('Module must inherit from ' . $this->parentClass);
        }
    }

    /**
     * @param string $type
     */
    protected function defineModuleOperation($type)
    {
        if ($type == 'install') {
            $this->preMethod = 'up';
        } else if ($type == 'uninstall') {
            $this->preMethod = 'down';
        } else if ($type == 'update') {
            $this->preMethod = 'update';
        }
    }


    public abstract function execute();
}