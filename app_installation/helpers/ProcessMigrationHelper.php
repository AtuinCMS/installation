<?php

namespace atuin\installation\app_installation\helpers;


use Symfony\Component\Process\ProcessBuilder;
use Yii;
use yii\base\Component;

class ProcessMigrationHelper extends CommandHelper
{
    protected function prepareBuilder()
    {
        $builder = $this->getCommandBuilder();
        $builder
            ->setWorkingDirectory(Yii::getAlias('@app'))
            ->setPrefix($this->phpInstantiation())
            ->setArguments([
               dirname( Yii::getAlias('@vendor') ). '/yii'
            ]);
        
        return $builder;
    }

    /**
     * Runs the migration of the selected directory in $data
     * 
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function execute($data, $type = 'up')
    {
        $builder = $this->prepareBuilder();

        $builder->add('migrate/' . $type);
        $builder->add('--interactive=0');
        $builder->add('--migrationPath=' . $data);

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
            $builder->add('migrate/history');
            $process = $builder->getProcess();
            $process->mustRun();

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