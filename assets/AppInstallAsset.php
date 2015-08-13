<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace atuin\installation\assets;

use yii\web\AssetBundle;

class AppInstallAsset extends AssetBundle
{

    public $sourcePath = '@atuin/installation';

    public $js = ['views/js/appInstallation.js'];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset'
    ];
}

