<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace atuin\installation\assets;

use yii\web\AssetBundle;

/**
 * Class AppAsset
 * @package atuin\engine\assets
 *
 * Using AdminLTE as explained in:
 * http://www.yiiframework.com/wiki/729/tutorial-about-how-to-integrate-yii2-with-fantastic-theme-adminlte/
 * 
 * 
 * AdminLTE info:
 * https://almsaeedstudio.com/AdminLTE
 *
 */
class AppAsset extends AssetBundle
{

    public $sourcePath = '@bower/';
    public $css = [
        'admin-lte/dist/css/AdminLTE.min.css',
        'admin-lte/dist/css/skins/_all-skins.min.css',
        'https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'
    ];
    public $js = ['admin-lte/dist/js/app.min.js'];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}
