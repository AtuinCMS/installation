<?php

namespace atuin\installation\app_installation;


use yii;

/**
 * Class AppInstaller
 *
 * Executes the App Managers passed via $managers parameter in the execute method.
 *
 * @package atuin\installation\app_installation
 */
class AppInstaller
{

    /**
     * Executes the managers passed via $managers parameter array
     *
     * @param \atuin\skeleton\Module $app
     * @param \atuin\installation\app_installation\BaseManagement[] $managers
     * @param string $type
     */
    public static function execute($app, $managers, $type = 'install')
    {
        $launchedManagers = [];

        foreach ($managers as $manager)
        {
            $launchedManagers[] = $manager;

            // if there is any problem installing the managers
            // we will uninstall all the already launched managers
//            try
//            {
                $manager->initialize($app, $type);
                $manager->execute();
//            } catch (\Exception $e)
//            {
//
//                self::execute($app, $launchedManagers, 'uninstall');
//
//                throw $e;
//            }

        }
    }

}