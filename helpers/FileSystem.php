<?php

namespace atuin\installation\helpers;

use League\Flysystem\Adapter\Local as Adapter;

class FileSystem
{

    static protected $_fileSystem;

    public static function fileSystem()
    {
        if (is_null(self::$_fileSystem))
        {
            self::$_fileSystem = new \League\Flysystem\Filesystem(
                new Adapter(\Yii::$app->getModule('installation')->getInstallationDirectory()));
        }

        return self::$_fileSystem;
    }

    public static function createFile($fileName, $contents)
    {
        $contents = '<?php
return ' . var_export($contents, TRUE) . ';';

        if (self::fileSystem()->has($fileName))
        {
            self::fileSystem()->update($fileName, $contents);
        } else
        {
            self::fileSystem()->write($fileName, $contents);
        }
    }

}