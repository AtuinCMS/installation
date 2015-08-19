<?php


namespace atuin\installation\helpers;

use atuin\installation\app_installation\helpers\FactoryCommandHelper;
use yii;

/**
 * Class ComposerAppHandler
 * @package atuin\installation\helpers
 *
 * Will handle the installation / uninstallation of composer packages
 * using the ProcessComposerHelper.
 *
 * In case the server can't handle composer it will use an alternate
 * system that will work with the files manually.
 * TODO make that :(
 *
 */
class ComposerAppHandler extends BaseAppHandler
{

    public $composerPackage;
    
    public function init()
    {
        parent::init();
        
        $this->directory = \Yii::$app->getVendorPath() . '/' . explode(':', $this->composerPackage)[0];
        
        if (is_null($this->composerPackage))
        {
            throw new yii\base\Exception('Composer package for App '.$this->id.' not defined.');
        }
    }

    /**
     * Gets the App using ProcessComposerHelper.
     * If there is any problem getting it, will throw an Exception and delete the downloaded data.
     * 
     * Uses Web First phylosophy, so could use another type of installation instead of Composer.
     * 
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function getApp()
    {
        try
        {
            // Composer installation
            if (FactoryCommandHelper::composer()->check())
            {
                FactoryCommandHelper::composer()->execute($this->composerPackage, 'up');
            }

        } catch (yii\base\Exception  $e)
        {
            $this->deleteApp();
            throw $e;
        }
    }

    /**
     * Updates the App using ProcessComposerHelper.
     * If there is any problem getting it, will throw an Exception.
     * 
     * Uses Web First phylosophy, so there should come with another type of installator instead of
     * Composer.
     * 
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function updateApp()
    {
        try
        {
            // Composer installation
            if (FactoryCommandHelper::composer()->check())
            {
                FactoryCommandHelper::composer()->execute($this->composerPackage, 'update');
            }

        } catch (yii\base\Exception  $e)
        {
            throw $e;
        }
    }

    /**
     * Deletes the App using ProcessComposerHelper
     * 
     * @throws \Exception
     * @throws yii\base\Exception
     */
    public function deleteApp()
    {
        try
        {
            if (FactoryCommandHelper::composer()->check())
            {
                FactoryCommandHelper::composer()->execute($this->composerPackage, 'down');
            }

        } catch (yii\base\Exception  $e)
        {
            throw $e;
        }
    }
    
}