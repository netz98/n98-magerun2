<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Code\Generator\ClassGenerator;

/**
 * Bridge command to a outer n98-magerun commands
 */
class CallCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('call')
            ->addArgument('command_string', InputArgument::IS_ARRAY, 'command string')
            ->setDescription('Calls a n98-magerun command in current context')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandStringArray = $input->getArgument('command_string');
        $commandStringArray = array_map('trim', $commandStringArray);

        $commandName = $commandStringArray[0];
        $proxyInput = new StringInput(implode(' ', array_slice($commandStringArray, 1)));

        return $this->callMagerunCommand($commandName, $proxyInput, $output);
    }
}