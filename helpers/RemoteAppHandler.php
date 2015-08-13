<?php

namespace atuin\installation\helpers;


class RemoteAppHandler extends BaseAppHandler
{

    // Right not we are going to focus on composer-only installations
    // In the future we may have a more friendly installation for servers
    // that don't let the use of composer and will use FlySystem to get the
    // contents from the web. 

    // $appSubdirectory = \Yii::$app->getModule('installation')->getInstallationDirectory() .
    //     \Yii::$app->getModule('installation')->getSubdirectories('apps');

    // $localAdapter = new Local($appSubdirectory);


    // $local = new \League\Flysystem\Filesystem($localAdapter);

    // // Add them in the MountManager
    // $manager = new \League\Flysystem\MountManager([
    //     'local' => $local,
    //     'source' => $appAdapter
    // ]);

    public function getApp()
    {

    }


    public function deleteApp()
    {

    }


    /**
     * Moves the contents of an Atuin App into the server's Atuin Filesystem
     *
     * @param \League\Flysystem\MountManager $manager
     */
    protected function moveAppContents($directory, $manager)
    {
        $contents = $manager->listContents('source://' . $directory);

        foreach ($contents as $entry)
        {
            if ($entry['type'] == 'dir')
            {
                $this->moveAppContents($entry['path'], $manager);
            } else
            {
                $manager->put('local://' . $entry['path'],
                    $manager->read('source://' . $entry['path']),
                    [
                        'visibility' => AdapterInterface::VISIBILITY_PUBLIC
                    ]);
            }
        }
    }

}