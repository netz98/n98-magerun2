<?php

namespace N98\Magento\Command\SubCommand;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubCommandFactory
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $commandConfig;

    /**
     * @var AbstractMagentoCommand
     */
    protected $command;

    /**
     * @param AbstractMagentoCommand $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array $commandConfig
     * @param ConfigBag $config
     */
    public function __construct(
        AbstractMagentoCommand $command,
        InputInterface $input,
        OutputInterface $output,
        array $commandConfig,
        ConfigBag $config
    ) {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
        $this->commandConfig = $commandConfig;
        $this->config = $config;
    }

    /**
     * @param string $relativeClassName
     * @return SubCommandInterface
     */
    public function create($relativeClassName)
    {
        $className = '\N98\Magento\Command\Installer\SubCommand\\' . $relativeClassName;

        $subCommand = new $className();
        if (! $subCommand instanceof SubCommandInterface) {
            throw new \InvalidArgumentException('Subcommand must implement SubCommandInterface.');
        }

        // Inject objects
        $subCommand->setCommand($this->command);
        $subCommand->setInput($this->input);
        $subCommand->setOutput($this->output);
        $subCommand->setConfig($this->config);
        $subCommand->setCommandConfig($this->commandConfig);

        return $subCommand;
    }
}