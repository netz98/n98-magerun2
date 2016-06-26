<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\BinaryString;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabase extends AbstractSubCommand
{
    /**
     * @var array
     */
    protected $_argv;

    /**
     * @var \Closure
     */
    protected $notEmptyCallback;

    /**
     * @return void
     */
    public function execute()
    {
        $this->notEmptyCallback = function ($input) {
            if (empty($input)) {
                throw new \InvalidArgumentException('Please enter a value');
            }
            return $input;
        };

        $dbOptions = array('--dbHost', '--dbUser', '--dbPass', '--dbName');
        $dbOptionsFound = 0;
        foreach ($dbOptions as $dbOption) {
            foreach ($this->getCliArguments() as $definedCliOption) {
                if (BinaryString::startsWith($definedCliOption, $dbOption)) {
                    $dbOptionsFound++;
                }
            }
        }

        $hasAllOptions = $dbOptionsFound == 4;

        // if all database options were passed in at cmd line
        if ($hasAllOptions) {
            $this->config->setString('db_host', $this->input->getOption('dbHost'));
            $this->config->setString('db_user', $this->input->getOption('dbUser'));
            $this->config->setString('db_pass', $this->input->getOption('dbPass'));
            $this->config->setString('db_name', $this->input->getOption('dbName'));
            $this->config->setInt('db_port', intval($this->input->getOption('dbPort')));
            $db = $this->validateDatabaseSettings($this->input, $this->output);

            if ($db === false) {
                throw new \InvalidArgumentException("Database configuration is invalid", null);
            }
        } else {
            $dialog = $this->getCommand()->getHelperSet()->get('dialog');
            do {

                // Host
                $dbHostDefault = $this->input->getOption('dbHost') ? $this->input->getOption('dbHost') : 'localhost';
                $this->config->setString(
                    'db_host',
                    $dialog->askAndValidate(
                        $this->output,
                        '<question>Please enter the database host</question> <comment>[' .
                        $dbHostDefault . ']</comment>: ',
                        $this->notEmptyCallback,
                        false,
                        $dbHostDefault
                    )
                );

                // Port
                $dbPortDefault = $this->input->getOption('dbPort') ? $this->input->getOption('dbPort') : 3306;
                $this->config->setInt(
                    'db_port',
                    intval($dialog->askAndValidate(
                        $this->output,
                        '<question>Please enter the database port </question> <comment>[' .
                        $dbPortDefault . ']</comment>: ',
                        $this->notEmptyCallback,
                        false,
                        $dbPortDefault
                    ))
                );

                // User
                $dbUserDefault = $this->input->getOption('dbUser') ? $this->input->getOption('dbUser') : 'root';
                $this->config->setString(
                    'db_user',
                    $dialog->askAndValidate(
                        $this->output,
                        '<question>Please enter the database username</question> <comment>[' .
                        $dbUserDefault . ']</comment>: ',
                        $this->notEmptyCallback,
                        false,
                        $dbUserDefault
                    )
                );

                // Password
                $dbPassDefault = $this->input->getOption('dbPass') ? $this->input->getOption('dbPass') : '';
                $this->config->setString(
                    'db_pass',
                    $dialog->ask(
                        $this->output,
                        '<question>Please enter the database password</question> <comment>[' .
                        $dbPassDefault . ']</comment>: ',
                        $dbPassDefault
                    )
                );

                // DB-Name
                $dbNameDefault = $this->input->getOption('dbName') ? $this->input->getOption('dbName') : 'magento';
                $this->config->setString(
                    'db_name',
                    $dialog->askAndValidate(
                        $this->output,
                        '<question>Please enter the database name</question> <comment>[' .
                        $dbNameDefault . ']</comment>: ',
                        $this->notEmptyCallback,
                        false,
                        $dbNameDefault
                    )
                );

                $db = $this->validateDatabaseSettings($this->input, $this->output);
            } while ($db === false);
        }

        $this->config->setObject('db', $db);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|\PDO
     */
    protected function validateDatabaseSettings(InputInterface $input, OutputInterface $output)
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s",
                $this->config->getString('db_host'),
                $this->config->getString('db_port')
            );

            $db = new \PDO($dsn, $this->config->getString('db_user'), $this->config->getString('db_pass'));

            $dbName = $this->config->getString('db_name');
            if (!$db->query('USE `' . $dbName . '`')) {
                $db->query("CREATE DATABASE `" . $dbName . "`");
                $output->writeln('<info>Created database ' . $dbName . '</info>');
                $db->query('USE `' . $dbName . '`');

                // Check DB version
                $statement = $db->query('SELECT VERSION()');
                $mysqlVersion = $statement->fetchColumn(0);
                if (version_compare($mysqlVersion, '5.6.0', '<')) {
                    throw new \Exception('MySQL Version must be >= 5.6.0');
                }

                return $db;
            }

            if ($input->getOption('noDownload') && !$input->getOption('forceUseDb')) {
                $output->writeln("<error>Database {$this->config->getString('db_name')} already exists.</error>");

                return false;
            }

            return $db;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } catch (\PDOException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCliArguments()
    {
        if ($this->_argv === null) {
            $this->_argv = $_SERVER['argv'];
        }

        return $this->_argv;
    }
}
