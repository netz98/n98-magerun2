<?php

namespace N98\Magento\Command\SubCommand;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSubCommand implements SubCommandInterface
{
    /**
     * @var ConfigBag
     */
    protected $config;

    /**
     * @var array
     */
    protected $commandConfig;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var AbstractMagentoCommand
     */
    protected $command;

    /**
     * @param ConfigBag $config
     */
    public function setConfig(ConfigBag $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->commandConfig = $commandConfig;
    }

    /**
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return AbstractMagentoCommand
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param AbstractMagentoCommand $command
     */
    public function setCommand(AbstractMagentoCommand $command)
    {
        $this->command = $command;
    }

    /**
     * @return void
     */
    abstract public function execute();
}
