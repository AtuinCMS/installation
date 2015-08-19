<?php

namespace atuin\installation\app_installation;

use atuin\installation\helpers\BaseAppHandler;
use yii;

/**
 * Class AppLoader
 *
 * Tries to load the App passed with it's name and location (optional in case the
 * App it's already located in the system)  returning the Yii2 module.
 *
 * @package atuin\installation\app_installation
 */
class AppLoader
{

    /**
     * Returns the loaded module
     *
     * @param $AppId
     * @param $appNameSpace
     * @param $appLocation
     * @return null|yii\base\Module
     * @throws yii\base\Exception
     */
    public function getModule($AppId, $appNameSpace, $appLocation)
    {

        // 1 - try to load the app Module File.
        $className = $appNameSpace . '\Module';

        if (!class_exists($className))
        {
            if (file_exists($appLocation . '/Module.php'))
            {
                include_once($appLocation . '/Module.php');
            }

            if (!class_exists($className))
            {
                throw new yii\base\Exception('Class ' . $className . ' doesn\'t exist. Maybe bad Namespace given?');
            }

        }

        // 2 - Create an Module object of the App.
        /** @var \atuin\skeleton\Module $installApp */
        $installApp = new $className($AppId);

        // 3 - Add its alias into the Yii2 system.
        $app_alias = '@' . $installApp->getAlias();

        Yii::$app->setAliases([$app_alias => $appLocation]);

        // 4 - Add the Module into the Yii2 system
        Yii::$app->setModule($installApp->getId(), ['class' => $className]);

        // 5 - Return the module.
        return Yii::$app->getModule($installApp->getId());
    }

    /**
     * Loads the App as a module moving it into the app subdirectory in case
     * it still isn't installed.
     *
     * @param $appIdentifier
     * @param NULL|BaseAppHandler $appHandler
     * @return null|yii\base\Module
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function loadApp($appIdentifier, BaseAppHandler $appHandler = NULL)
    {

        // 1 - Checks if the app it's already declared as a Yii2 module and if that's the case
        // will return it.

        if (!is_null(Yii::$app->getModule($appIdentifier)))
        {
            return Yii::$app->getModule($appIdentifier);
        }
        try
        {
            $appHandler->getApp();

            return $this->getModule($appHandler->id, $appHandler->namespace, $appHandler->directory);
        } catch (yii\base\Exception $e)
        {
            throw $e;
        }

    }

    /**
     * Updates the selected app.
     *
     * @param $appIdentifier
     * @param BaseAppHandler $appHandler
     * @return null|yii\base\Module
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function updateApp($appIdentifier, BaseAppHandler $appHandler)
    {
        try
        {
            // 1 - Updates the App code
            $appHandler->updateApp();

            // 2 - Reloads the Module because there will be new data neccesary to 
            // implement in the new installation
            return $this->getModule($appHandler->id, $appHandler->namespace, $appHandler->directory);
        } catch (yii\base\Exception $e)
        {
            throw $e;
        }
    }

}