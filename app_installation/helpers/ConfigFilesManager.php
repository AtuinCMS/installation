<?php

namespace atuin\installation\app_installation\helpers;


use atuin\apps\models\ModelApp;
use atuin\config\models\ModelConfig;
use atuin\installation\helpers\FileSystem;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class ConfigFilesManager
 * @package atuin\installation\app_installation\helpers
 * 
 * Creates or updates the config files for app-backend and app-frontend.
 * 
 * Should be launched each time we make an app installation - uninstallation
 * or we make an important change in the atuin config table.
 * 
 */
class ConfigFilesManager extends Component
{

    public static function generateConfigFiles()
    {
        foreach (['app-backend', 'app-frontend'] as $type)
        {
            // LOAD APP DATA
            $config = ArrayHelper::merge(
                [],
                ModelConfig::getActiveSectionConfigs($type)
            );

            $configPath = \Yii::$app->getModule('installation')->getSubdirectories('config');

            FileSystem::createFile($configPath . '/' . $type . '.php', $config);

        }
    }

}