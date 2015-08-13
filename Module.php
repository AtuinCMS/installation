<?php

namespace atuin\installation;

use atuin\installation\libraries\InstallationManager;
use Yii;

/**
 * Class Module
 *
 * Module that will handle the basic installation of the Atuin system.
 *
 * @package atuin\installation
 */
class Module extends \atuin\skeleton\Module
{

    protected static $_id = 'installation';

    protected static $_version = '0';

    public $is_core_module = 1;

    private $_subDirectories = [
        'routes' => 'routes',
        'config' => 'config'
    ];


    private $_installationDirectory;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->_installationDirectory = dirname(Yii::$app->getVendorPath()) . '/atuin';

    }


    public function checkInstallation()
    {
        return InstallationManager::checkInstallation();
    }

    public function getInstallationDirectory($subdirectory = NULL)
    {

        $subdirectoryUrl = '';

        if (!is_null($subdirectory))
        {
            $subdirectoryUrl = $this->_subDirectories[$subdirectory] . '/';
        }

        return $this->_installationDirectory . '/' . $subdirectoryUrl;
    }

    public function getSubdirectories($subdirectory = NULL)
    {
        if (is_null($subdirectory))
        {
            return $this->_subDirectories;
        }

        return $this->_subDirectories[$subdirectory];
    }

    public function launchInstallation()
    {
        $installationManager = new InstallationManager();

        return $installationManager->execute();
    }

}
