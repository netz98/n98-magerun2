<?php

namespace N98\Magento\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class KeepCalmCommand extends AbstractMagentoCommand
{
    protected function configure(): void
    {
        $this
            ->setName('keep:calm')
            ->setDescription(
                'Run cache clean, reindex, setup upgrade, di compile and static content deploy'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commands = [
            'cache:clean',
            'indexer:reindex',
            'setup:upgrade',
            'setup:di:compile',
            'setup:static-content:deploy',
        ];

        foreach ($commands as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $exitCode = $command->run(new StringInput($commandName), $output);
            if (Command::SUCCESS !== $exitCode) {
                return $exitCode;
            }
        }

        return Command::SUCCESS;
    }
}
