<?php


namespace atuin\installation\app_installation\helpers;


use Yii;

/**
 * Class ProcessComposerHelper
 *
 * This class it's a little tricky due to the wide range of composer configurations that exist.
 * It may cause errors.
 *
 *
 * @package atuin\installation\app_installation\helpers
 */
class ProcessComposerHelper extends CommandHelper
{
    public $composerHomeDirectory = './.composer/';

    protected function prepareBuilder()
    {
        $builder = $this->getCommandBuilder();

        $builder
            ->setEnv('COMPOSER_HOME', $this->composerHomeDirectory)
            ->setWorkingDirectory(dirname(Yii::getAlias('@vendor')))
            ->setPrefix($this->phpInstantiation())
            ->setArguments([
                dirname(Yii::getAlias('@vendor')) . '/./'
                . '/composer.phar',
                '--verbose',
                '--no-interaction'
            ]);

        return $builder;
    }


    /**
     * Runs the composer installation of the selected package in $data
     *
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function execute($data, $type = 'up')
    {
        // 1 - Run process to remove cache from composer, because 
        // it makes strange things when dealing with www-data
        $builder = $this->prepareBuilder();
        $builder->add('clear-cache');
        $this->runProccess($builder);


        // 2 - Make the proper composer execution now that the
        // cache should be cleared (note that this can not be really true
        // people has reported that some .cache dirs may still remain
        // with data).
        $builder = $this->prepareBuilder();

        if ($type == 'up')
        {
            $builder->add('require');
            $builder->add('--prefer-source');
        }
        elseif ($type == 'update')
        {
            $builder->add('update');
            $builder->add('--prefer-source');
        }
        else
        {
            $builder->add('remove');
        }

        $builder->add($data);

        return $this->runProccess($builder)->getOutput();

    }

    /**
     * Checks the system with Symfony's ProccessBuilder to see if the
     * server it's able to use that class. If it isn't it will return
     * a False.
     *
     * @return bool
     */
    public function check()
    {
        try
        {
            $builder = $this->prepareBuilder();
            $builder->add('-h');

            $process = $this->runProccess($builder);

            // executes after the command finishes
            if (!$process->isSuccessful())
            {
                return FALSE;
            }

        } catch (\Exception $e)
        {
            return FALSE;
        }

        return TRUE;
    }

}