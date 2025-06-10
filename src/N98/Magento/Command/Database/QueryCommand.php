<?php

namespace N98\Magento\Command\Database;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class QueryCommand
 * @package N98\Magento\Command\Database
 */
class QueryCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:query')
            ->addArgument('query', InputArgument::OPTIONAL, 'SQL query')
            ->addOption('only-command', null, InputOption::VALUE_NONE, 'Print only mysql command. Do not execute')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output format (e.g., csv)'
            )
            ->setDescription('Executes an SQL query on the database defined in env.php');

        $help = <<<HELP
Executes an SQL query on the current configured database. Wrap your SQL in
single or double quotes.

If your query produces a result (e.g. a SELECT statement), the output of the
mysql cli tool will be returned.

* Requires MySQL CLI tools installed on your system.

HELP;
        $this->setHelp($help);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec');
    }

    /**
     * Returns the query string with escaped ' characters so it can be used
     * within the mysql -e argument.
     *
     * The -e argument is enclosed by single quotes. As you can't escape
     * the single quote within the single quote, you have to end the quote,
     * then escape the single quote character and reopen the quote.
     *
     * @param string $query
     * @return string
     */
    protected function getEscapedSql($query)
    {
        return str_replace("'", "'\\''", $query);
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

        if (($query = $input->getArgument('query')) === null) {
            /** @var $questionHelper QuestionHelper */
            $questionHelper = $this->getHelper('question');
            $question = new Question('<question>SQL Query:</question>');
            $query = $questionHelper->ask($input, $output, $question);
        }

        $query = $this->getEscapedSql($query);

        $exec = 'mysql ' . $this->getDatabaseHelper()->getMysqlClientToolConnectionString() . " -e '" . $query . "'";

        if ($input->getOption('only-command')) {
            $output->writeln($exec);
            $returnValue = 0;
        } else {
            $format = $input->getOption('format');
            if ($format === 'csv') {
                // Prepend -B for batch mode (tab-separated output)
                $exec = str_replace('mysql ', 'mysql -B ', $exec);
                exec($exec, $commandOutput, $returnValue);

                if ($returnValue === 0) {
                    foreach ($commandOutput as $line) {
                        $parts = explode("\t", $line);
                        $csvLine = '"' . implode('","', $parts) . '"';
                        $output->writeln($csvLine);
                    }
                } else {
                    $output->writeln('<error>' . implode(PHP_EOL, $commandOutput) . '</error>');
                }
            } else {
                exec($exec, $commandOutput, $returnValue);
                $output->writeln($commandOutput);
                if ($returnValue > 0) {
                    $output->writeln('<error>' . implode(PHP_EOL, $commandOutput) . '</error>');
                }
            }
        }

        return $returnValue;
    }
}
