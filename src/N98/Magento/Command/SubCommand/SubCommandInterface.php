<?php

namespace N98\Magento\Command\SubCommand;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface SubCommandInterface
{
    /**
     * @param ConfigBag $config
     */
    public function setConfig(ConfigBag $config);

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig);

    /**
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input);

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output);

    /**
     * @return AbstractMagentoCommand
     */
    public function getCommand();

    /**
     * @param AbstractMagentoCommand $command
     */
    public function setCommand(AbstractMagentoCommand $command);

    /**
     * @return bool
     */
    public function execute();
}
