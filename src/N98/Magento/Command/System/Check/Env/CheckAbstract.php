<?php

namespace N98\Magento\Command\System\Check\Env;

use Adbar\Dot;
use N98\Magento\Command\CommandAware;
use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;
use N98\Magento\Command\System\CheckCommand;
use Symfony\Component\Console\Command\Command;

abstract class CheckAbstract implements SimpleCheck, CommandAware, CommandConfigAware
{
    /**
     * @var ResultCollection
     */
    protected $_results;

    /**
     * @var array
     */
    protected $_commandConfig;

    /**
     * @var CheckCommand
     */
    protected $_checkCommand;

    /**
     * @var Dot
     */
    protected $_dot;

    /**
     * @var array
     */
    protected $_env;

    /**
     * @var string
     */
    protected $_envFilePath;

    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results)
    {
        $this->_results = $results;
        $this->_envFilePath = $this->_checkCommand->getApplication()->getMagentoRootFolder() . '/app/etc/env.php';

        $envArray = include $this->_envFilePath;
        $this->_env = $envArray;
        $this->_dot = new Dot($envArray);

        $this->checkEnv();
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->_commandConfig = $commandConfig;
    }

    /**
     * @param Command $command
     */
    public function setCommand(Command $command)
    {
        $this->_checkCommand = $command;
    }
}
