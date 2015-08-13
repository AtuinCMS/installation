<?php

namespace atuin\installation\app_installation\helpers;


use Symfony\Component\Process\ProcessBuilder;
use Yii;
use yii\base\Component;
use Symfony\Component\Process\Process;

abstract class CommandHelper extends Component
{

    /**
     * Instantiates a Synfony's Proccess builder and returns it.
     *
     * @return ProcessBuilder
     */
    protected function getCommandBuilder()
    {
        $builder = new ProcessBuilder();

        return $builder;
    }

    /**
     * Instantiates PHP path to launch commands
     *
     * @return string
     */
    protected function phpInstantiation()
    {
        return PHP_BINDIR . '/php';
    }

    /**
     * @param ProcessBuilder $builder
     * @return Process
     */
    protected function runProccess($builder)
    {
        $proccess = $builder->getProcess();
        $proccess->mustRun();

        return $proccess;
    }


    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public abstract function execute($data, $type = 'up');


    /**
     * Checks if the class can execute the ProccessBuilder command
     * 
     * @return boolean
     */
    public abstract function check();
    
}