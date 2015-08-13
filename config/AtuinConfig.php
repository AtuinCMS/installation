<?php

namespace atuin\installation\config;

use atuin\config\models\ModelConfig;
use yii\base\Component;

/**
 * Class ConfigSkeleton
 * @package common\engine\module_skeleton\libraries
 *
 * Class called to install a module in the CMS.
 *
 * Here must be all the automatic changes in the system that will be necessary to install a new module.
 *
 */
class AtuinConfig extends \atuin\skeleton\config\AtuinConfig
{

    /**
     * @inheritdoc
     */
    public function upMigration()
    {

    }

    /**
     * @inheritdoc
     */
    public function downMigration()
    {

    }

    /**
     * @inheritdoc
     */
    public function upMenu()
    {

    }


    /**
     * @inheritdoc
     */
    public function downMenu()
    {

    }

    /**
     * @inheritdoc
     */
    public function upConfig()
    {
    }


    /**
     * @inheritdoc
     */
    public function downConfig()
    {
        $this->configItems->deleteConfig();
    }

    /**
     * @inheritdoc
     */
    public function upManual()
    {

    }


    /**
     * @inheritdoc
     */
    public function downManual()
    {

    }

}