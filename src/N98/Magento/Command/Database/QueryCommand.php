<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use function Laravel\Prompts\text;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
     * Strips a single pair of wrapping quotes from the query, as recommended by this
     * command's own --help text (e.g. `db:query "SELECT 1"`). A real shell already
     * consumes those quotes before PHP ever sees the argument, but callers that pass
     * the argument through directly (e.g. the MCP tool) forward them literally, which
     * would otherwise break the SQL. A genuine query never starts and ends with the
     * same quote character wrapping the entire statement, so stripping is safe.
     *
     * @param string $query
     * @return string
     */
    protected function stripWrappingQuotes($query)
    {
        $length = strlen($query);
        if ($length < 2) {
            return $query;
        }

        $first = $query[0];
        $last = $query[$length - 1];

        if (($first === '"' || $first === "'") && $first === $last) {
            return substr($query, 1, -1);
        }

        return $query;
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
            if (!$input->isInteractive()) {
                throw new RuntimeException('No SQL query provided. Please pass the query as a command argument.');
            }
            $query = text('SQL Query:');
        }

        $query = $this->stripWrappingQuotes($query);
        $query = $this->getEscapedSql($query);

        $exec = 'mysql ' . $this->getDatabaseHelper()->getMysqlClientToolConnectionString() . " -e '" . $query . "'";

        if ($input->getOption('only-command')) {
            $output->writeln($exec);
            return 0;
        }

        $format = $input->getOption('format');
        if ($format === 'csv') {
            // Prepend -B for batch mode (tab-separated output)
            $exec = str_replace('mysql ', 'mysql -B ', $exec);
        }

        $process = Process::fromShellCommandline($exec);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            if ($errorOutput === '') {
                $errorOutput = trim($process->getOutput());
            }
            $output->writeln('<error>' . $errorOutput . '</error>');

            return $process->getExitCode() ?: 1;
        }

        $commandOutput = rtrim($process->getOutput(), "\r\n");
        if ($commandOutput === '') {
            return 0;
        }

        if ($format === 'csv') {
            foreach (explode("\n", $commandOutput) as $line) {
                $parts = explode("\t", rtrim($line, "\r"));
                $csvLine = '"' . implode('","', $parts) . '"';
                $output->writeln($csvLine);
            }
        } else {
            $output->writeln($commandOutput);
        }

        return 0;
    }
}
