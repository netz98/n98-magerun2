<?php

namespace N98\Magento\Command\Database;

use N98\Util\Console\Helper\DatabaseHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DropCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:drop')
            ->addOption('tables', 't', InputOption::VALUE_NONE, 'Drop all tables instead of dropping the database')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force')
            ->setDescription('Drop current database');

        $help = <<<HELP
The command prompts before dropping the database. If --force option is specified it
directly drops the database.
The configured user in app/etc/env.php must have "DROP" privileges.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);

        /** @var $questionHelper QuestionHelper */
        $questionHelper = $this->getHelper('question');

        /** @var $dbHelper DatabaseHelper */
        $dbHelper = $this->getHelper('database');

        if ($input->getOption('force')) {
            $shouldDrop = true;
        } else {
            $question = new ConfirmationQuestion(
                sprintf(
                    '<question>Really drop database %s ? (y/n)</question> <comment>[n]</comment>: ',
                    $this->dbSettings['dbname']
                ),
                false
            );

            $shouldDrop = $questionHelper->ask(
                $input,
                $output,
                $question
            );
        }

        if ($shouldDrop) {
            if ($input->getOption('tables')) {
                $dbHelper->dropTables($output);
            } else {
                $dbHelper->dropDatabase($output);
            }
        }
    }
}
