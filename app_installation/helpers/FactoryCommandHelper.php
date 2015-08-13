<?php

namespace atuin\installation\app_installation\helpers;


class FactoryCommandHelper
{

    private static $processMigrationHelper;

    private static $processComposerHelper;

    /**
     * This method will be used in the future to host possible
     * alternate methods to install migrations when the server
     * installation can't handle Symfony ProcessBuilder.
     *
     * Instead of returning the ProcessMigrationHelper will return
     * another object in those cases.
     *
     * @return ProcessMigrationHelper
     */
    public static function migration()
    {
        if (is_null(self::$processMigrationHelper))
        {
            self::$processMigrationHelper = new ProcessMigrationHelper();
        }

        return self::$processMigrationHelper;
    }

    /**
     * This method will be used in the future to host possible
     * alternate methods to execute composer installations when
     * the server installation can't handle composer calls.
     *
     * Instead of returning the ProcessComposerHelper will return
     * another object in those cases.
     *
     * @return ProcessComposerHelper
     */
    public static function composer()
    {
        if (is_null(self::$processComposerHelper))
        {
            self::$processComposerHelper = new ProcessComposerHelper();
        }

        return self::$processComposerHelper;
    }


}