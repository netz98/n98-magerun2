<?php

namespace N98\Magento\Command\Database;

use InvalidArgumentException;
use N98\Util\Console\Helper\DatabaseHelper;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InfoCommand
 * @package N98\Magento\Command\Database
 */
class InfoCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:info')
            ->addArgument('setting', InputArgument::OPTIONAL, 'Only output value of named setting')
            ->setDescription('Dumps database informations')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
        $this->addDeprecatedAlias('database:info', 'Please use db:info');

        $help = <<<HELP
This command is useful to print all informations about the current configured database in app/etc/env.php.
It can print connection string for JDBC, PDO connections.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);

        $settings = [];
        foreach ($this->dbSettings as $key => $value) {
            if (is_array($value)) {
                if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                    $output->writeln(sprintf("<error>Skipping db-settings key '%s' as being array</error>", $key));
                }
                continue;
            }
            $settings[$key] = (string) $value;
        }

        $isSocketConnect = $this->isSocketConnect;

        // note: there is no need to specify the default port neither for PDO, nor JDBC nor CLI.
        $portOrDefault = isset($this->dbSettings['port']) ? $this->dbSettings['port'] : 3306;

        if ($isSocketConnect) {
            $pdoConnectionString = sprintf(
                'mysql:unix_socket=%s;dbname=%s',
                $this->dbSettings['unix_socket'],
                $this->dbSettings['dbname']
            );
        } else {
            $pdoConnectionString = sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                $this->dbSettings['host'],
                $portOrDefault,
                $this->dbSettings['dbname']
            );
        }
        $settings['PDO-Connection-String'] = $pdoConnectionString;

        if ($isSocketConnect) {
            // isn't supported according to this post: http://stackoverflow.com/a/18493673/145829
            $jdbcConnectionString = 'Connecting using JDBC through a unix socket isn\'t supported!';
        } else {
            $jdbcConnectionString = sprintf(
                'jdbc:mysql://%s:%s/%s?username=%s&password=%s',
                $this->dbSettings['host'],
                $portOrDefault,
                $this->dbSettings['dbname'],
                $this->dbSettings['username'],
                $this->dbSettings['password']
            );
        }
        $settings['JDBC-Connection-String'] = $jdbcConnectionString;

        /* @var $database DatabaseHelper */
        $database = $this->getHelper('database');
        $mysqlCliString = 'mysql ' . $database->getMysqlClientToolConnectionString();
        $settings['MySQL-Cli-String'] = $mysqlCliString;

        $rows = [];
        foreach ($settings as $settingName => $settingValue) {
            $rows[] = [$settingName, $settingValue];
        }

        if (($settingArgument = $input->getArgument('setting')) !== null) {
            if (!isset($settings[$settingArgument])) {
                throw new InvalidArgumentException('Unknown setting: ' . $settingArgument);
            }
            $output->writeln((string) $settings[$settingArgument]);
        } else {
            $this->getHelper('table')
                ->setHeaders(['Name', 'Value'])
                ->renderByFormat($output, $rows, $input->getOption('format'));
        }

        return Command::SUCCESS;
    }
}
