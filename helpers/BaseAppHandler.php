<?php

namespace atuin\installation\helpers;

use yii\base\Component;

/**
 * Class BaseAppHandler
 * @package atuin\installation\helpers
 *
 * This class will take care of the operations to get the new Apps
 * into the directory system from various sources like FTP, WWW or Composer
 * using the philosophy of "Web First".
 *
 */
abstract class BaseAppHandler extends Component
{
    /**
     * App Unique Identifier.
     * 
     * Warning: beware of naming collisions.
     * 
     * It's recommended to use atuin module naming convention like:
     *  
     * "atuin-users" or "atuin-blog" to ensure that this modules won't
     * have the same name of any other installed module.
     * 
     * @var string 
     */
    public $id;

    /**
     * Directory where the app will be hosted. 
     * 
     * @var String
     */
    public $directory;

    /**
     * App Namespace
     * 
     * Needed to load the Module Class when making it's installation.
     * 
     * @var String
     */
    public $namespace;

    public abstract function getApp();
    
    public abstract function deleteApp();

}