<?php

namespace N98\Magento\Command\Database;

use N98\Util\Console\Helper\DatabaseHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class DropCommand
 * @package N98\Magento\Command\Database
 */
class DropCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:drop')
            ->addOption('tables', 't', InputOption::VALUE_NONE, 'Drop all tables instead of dropping the database')
            ->addOption('drop-views', null, InputOption::VALUE_NONE, 'Drop all views in the database')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force')
            ->setDescription('Drop current database, tables or views');

        $help = <<<HELP
The command prompts before dropping the database/tables/views.
If --force option is specified it directly performs the drop operation.
The configured user in app/etc/env.php must have "DROP" privileges.

Usage:
  db:drop                                   (Prompts to drop the entire database)
  db:drop --force                           (Drops the entire database without prompting)
  db:drop --tables                          (Prompts to drop all tables)
  db:drop --tables --force                  (Drops all tables without prompting)
  db:drop --drop-views                      (Prompts to drop all views)
  db:drop --drop-views --force              (Drops all views without prompting)
  db:drop --tables --drop-views             (Prompts to drop all tables and all views)
  db:drop --tables --drop-views --force     (Drops all tables and all views without prompting)

If neither --tables nor --drop-views is specified, the entire database will be dropped.
If --tables is specified, only tables will be dropped (unless --drop-views is also specified).
If --drop-views is specified, only views will be dropped (unless --tables is also specified).
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
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
            $droppedSomething = false;
            if ($input->getOption('tables')) {
                $dbHelper->dropTables($output);
                $droppedSomething = true;
            }
            if ($input->getOption('drop-views')) {
                $dbHelper->dropViews($output);
                $droppedSomething = true;
            }

            // If neither --tables nor --drop-views was specified, then drop the entire database.
            if (!$droppedSomething) {
                $dbHelper->dropDatabase($output);
            }
        }

        return Command::SUCCESS;
    }
}
