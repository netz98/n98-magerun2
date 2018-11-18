<?php

namespace N98\Util\Console\Helper;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseHelper
 *
 * @package N98\Util\Console\Helper
 */
class DatabaseHelper extends AbstractHelper
{
    /**
     * @var array
     */
    protected $dbSettings = null;

    /**
     * @var bool
     */
    protected $isSocketConnect = false;

    /**
     * @var PDO
     */
    protected $_connection = null;

    /**
     * @var array
     */
    protected $_tables;

    /**
     * @var string
     */
    private $connectionType = 'default';

    /**
     * Set connection type when several db used.
     *
     * @param $connectionType
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;
    }

    /**
     * @param OutputInterface|null $output
     *
     * @throws RuntimeException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    public function detectDbSettings(OutputInterface $output)
    {
        if (null !== $this->dbSettings) {
            return;
        }

        /* @var $magentoHelper MagentoHelper */
        $magentoHelper = $this->getHelperSet()->get('magento');
        $config = $magentoHelper->getBaseConfig(); // @TODO Use \Magento\Framework\App\DeploymentConfig ?

        if (!isset($config['db'])) {
            throw new RuntimeException('DB settings was not found in app/etc/env.php file');
        }

        if (!isset($config['db']['connection'][$this->connectionType])) {
            throw new RuntimeException(
                sprintf('Cannot find "%s" connection config in app/etc/env.php', $this->connectionType)
            );
        }

        $this->dbSettings = (array) $config['db']['connection'][$this->connectionType];

        $this->dbSettings['prefix'] = '';
        if (isset($config['db']['table_prefix'])) {
            $this->dbSettings['prefix'] = (string) $config['db']['table_prefix'];
        }

        if (
            isset($this->dbSettings['host'])
            && strpos($this->dbSettings['host'], ':') !== false
        ) {
            list($this->dbSettings['host'], $this->dbSettings['port']) = explode(':', $this->dbSettings['host']);
        }

        if (isset($this->dbSettings['comment'])) {
            unset($this->dbSettings['comment']);
        }

        if (isset($this->dbSettings['unix_socket'])) {
            $this->isSocketConnect = true;
        }
    }

    /**
     * Connects to the database without initializing magento
     *
     * @param OutputInterface $output = null
     *
     * @return PDO
     * @throws RuntimeException pdo mysql extension is not installed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getConnection(OutputInterface $output = null)
    {
        $output = $this->fallbackOutput($output);

        if ($this->_connection) {
            return $this->_connection;
        }

        $this->detectDbSettings($output);

        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('pdo_mysql extension is not installed');
        }

        if (strpos($this->dbSettings['host'], '/') !== false) {
            $this->dbSettings['unix_socket'] = $this->dbSettings['host'];
            unset($this->dbSettings['host']);
        } elseif (strpos($this->dbSettings['host'], ':') !== false) {
            list($this->dbSettings['host'], $this->dbSettings['port']) = explode(':', $this->dbSettings['host']);
        }

        $this->_connection = new PDO(
            $this->dsn(),
            $this->dbSettings['username'],
            $this->dbSettings['password']
        );

        /** @link http://bugs.mysql.com/bug.php?id=18551 */
        $this->_connection->query("SET SQL_MODE=''");

        try {
            $this->_connection->query('USE `' . $this->dbSettings['dbname'] . '`');
        } catch (PDOException $e) {
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln(sprintf(
                    '<error>Failed to use database <comment>%s</comment>: %s</error>',
                    var_export($this->dbSettings['dbname'], true),
                    $e->getMessage()
                ));
            }
        }

        $this->_connection->query('SET NAMES utf8');

        $this->_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $this->_connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        return $this->_connection;
    }

    /**
     * Creates a PDO DSN for the adapter from $this->_config settings.
     *
     * @see Zend_Db_Adapter_Pdo_Abstract
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function dsn()
    {
        $this->detectDbSettings($this->fallbackOutput());

        // baseline of DSN parts
        $dsn = $this->dbSettings;

        // don't pass the username, password, charset, database, persistent and driver_options in the DSN
        unset($dsn['username']);
        unset($dsn['password']);
        unset($dsn['options']);
        unset($dsn['charset']);
        unset($dsn['persistent']);
        unset($dsn['driver_options']);
        unset($dsn['dbname']);

        // use all remaining parts in the DSN
        $buildDsn = array();
        foreach ($dsn as $key => $val) {
            if (is_array($val)) {
                continue;
            }
            $buildDsn[$key] = "$key=$val";
        }

        return 'mysql:' . implode(';', $buildDsn);
    }

    /**
     * Check whether current mysql user has $privilege privilege
     *
     * @param string $privilege
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function mysqlUserHasPrivilege($privilege)
    {
        $statement = $this->getConnection()->query('SHOW GRANTS');

        $result = $statement->fetchAll(PDO::FETCH_COLUMN);
        foreach ($result as $row) {
            if (preg_match('/^GRANT(.*)' . strtoupper($privilege) . '/', $row)
                || preg_match('/^GRANT(.*)ALL/', $row)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getMysqlClientToolConnectionString()
    {
        $this->detectDbSettings($this->fallbackOutput());

        if ($this->isSocketConnect) {
            $string = '--socket=' . escapeshellarg($this->dbSettings['unix_socket']);
        } else {
            $string = '-h' . escapeshellarg($this->dbSettings['host']);
        }

        $string .= ' '
            . '-u' . escapeshellarg($this->dbSettings['username'])
            . ' '
            . (isset($this->dbSettings['port'])
                ? '-P' . escapeshellarg($this->dbSettings['port']) . ' ' : '')
            . (strlen($this->dbSettings['password'])
                ? '--password=' . escapeshellarg($this->dbSettings['password']) . ' ' : '')
            . escapeshellarg($this->dbSettings['dbname']);

        return $string;
    }

    /**
     * Get mysql variable value
     *
     * @param string $variable
     *
     * @return bool|array returns array on success, false on failure
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getMysqlVariableValue($variable)
    {
        $statement = $this->getConnection()->query("SELECT @@{$variable};");
        if (false === $statement) {
            throw new RuntimeException(sprintf('Failed to query mysql variable %s', var_export($variable, 1)));
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     * obtain mysql variable value from the database connection.
     *
     * in difference to @see getMysqlVariableValue(), this method allows to specify the type of the variable as well
     * as to use any variable identifier even such that need quoting.
     *
     * @param string $name mysql variable name
     * @param string|null $type [optional] variable type, can be a system variable ("@@", default) or a session variable
     *                     ("@").
     *
     * @return string variable value, null if variable was not defined
     * @throws RuntimeException in case a system variable is unknown (SQLSTATE[HY000]: 1193: Unknown system variable
     * 'nonexistent')
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getMysqlVariable($name, $type = null)
    {
        if (null === $type) {
            $type = '@@';
        } else {
            $type = (string) $type;
        }

        if (!in_array($type, array('@@', '@'), true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid mysql variable type "%s", must be "@@" (system) or "@" (session)', $type)
            );
        }

        $quoted = '`' . strtr($name, array('`' => '``')) . '`';
        $query = "SELECT {$type}{$quoted};";

        $connection = $this->getConnection();
        $statement = $connection->query($query, PDO::FETCH_COLUMN, 0);
        if ($statement instanceof PDOStatement) {
            $result = $statement->fetchColumn(0);
        } else {
            $reason = $connection->errorInfo()
                ? vsprintf('SQLSTATE[%s]: %s: %s', $connection->errorInfo())
                : 'no error info';

            throw new RuntimeException(
                sprintf('Failed to query mysql variable %s: %s', var_export($name, true), $reason)
            );
        }

        return $result;
    }

    /**
     * @param array $commandConfig
     *
     * @throws RuntimeException
     * @return array
     */
    public function getTableDefinitions(array $commandConfig)
    {
        $tableDefinitions = array();
        if (!isset($commandConfig['table-groups'])) {
            return $tableDefinitions;
        }

        $tableGroups = $commandConfig['table-groups'];
        foreach ($tableGroups as $index => $definition) {
            if (!isset($definition['id'])) {
                throw new RuntimeException("Invalid definition of table-groups (id missing) at index: $index");
            }
            $id = $definition['id'];
            if (isset($tableDefinitions[$id])) {
                throw new RuntimeException("Invalid definition of table-groups (duplicate id) id: $id");
            }

            if (!isset($definition['tables'])) {
                throw new RuntimeException("Invalid definition of table-groups (tables missing) id: $id");
            }
            $tables = $definition['tables'];

            if (is_string($tables)) {
                $tables = preg_split('~\s+~', $tables, -1, PREG_SPLIT_NO_EMPTY);
            }
            if (!is_array($tables)) {
                throw new RuntimeException("Invalid tables definition of table-groups id: $id");
            }
            $tables = array_map('trim', $tables);

            $description = isset($definition['description']) ? $definition['description'] : '';

            $tableDefinitions[$id] = array(
                'tables'      => $tables,
                'description' => $description,
            );
        }

        return $tableDefinitions;
    }

    /**
     * @param array $list to resolve
     * @param array $definitions from to resolve
     * @param array $resolved Which definitions where already resolved -> prevent endless loops
     *
     * @return array
     * @throws RuntimeException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function resolveTables(array $list, array $definitions = array(), array $resolved = array())
    {
        if ($this->_tables === null) {
            $this->_tables = $this->getTables(true);
        }

        $resolvedList = array();
        foreach ($list as $entry) {
            if (substr($entry, 0, 1) == '@') {
                $code = substr($entry, 1);
                if (!isset($definitions[$code])) {
                    throw new RuntimeException('Table-groups could not be resolved: ' . $entry);
                }
                if (!isset($resolved[$code])) {
                    $resolved[$code] = true;
                    $tables = $this->resolveTables(
                        $this->resolveRetrieveDefinitionsTablesByCode($definitions, $code),
                        $definitions,
                        $resolved
                    );
                    $resolvedList = array_merge($resolvedList, $tables);
                }
                continue;
            }

            // resolve wildcards
            if (strpos($entry, '*') !== false) {
                $connection = $this->getConnection();
                $sth = $connection->prepare(
                    'SHOW TABLES LIKE :like',
                    array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
                );
                $sth->execute(
                    array(':like' => str_replace('*', '%', $this->dbSettings['prefix'] . $entry))
                );
                $rows = $sth->fetchAll();
                foreach ($rows as $row) {
                    $resolvedList[] = $row[0];
                }
                continue;
            }

            if (in_array($entry, $this->_tables)) {
                $resolvedList[] = $this->dbSettings['prefix'] . $entry;
            }
        }

        asort($resolvedList);
        $resolvedList = array_unique($resolvedList);

        return $resolvedList;
    }

    /**
     * @param array $definitions
     * @param string $code
     * @return array tables
     */
    private function resolveRetrieveDefinitionsTablesByCode(array $definitions, $code)
    {
        $tables = $definitions[$code]['tables'];

        if (is_string($tables)) {
            $tables = preg_split('~\s+~', $tables, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($tables)) {
            throw new RuntimeException("Invalid tables definition of table-groups code: @$code");
        }

        $tables = array_reduce((array) $tables, array($this, 'resolveTablesArray'), null);

        return $tables;
    }

    /**
     * @param array|null $carry [optional]
     * @param $item [optional]
     * @return array
     * @throws InvalidArgumentException if item is not an array or string
     */
    private function resolveTablesArray(array $carry = null, $item = null)
    {
        if (is_string($item)) {
            $item = preg_split('~\s+~', $item, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (is_array($item)) {
            if (count($item) > 1) {
                $item = array_reduce($item, array($this, 'resolveTablesArray'), (array) $carry);
            }
        } else {
            throw new InvalidArgumentException(sprintf('Unable to handle %s', var_export($item, true)));
        }

        return array_merge((array) $carry, $item);
    }

    /**
     * Get list of database tables
     *
     * @param bool|null $withoutPrefix [optional] remove prefix from the returned table names. prefix is obtained from
     *                                 magento database configuration. defaults to false.
     *
     * @return array
     * @throws RuntimeException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getTables($withoutPrefix = null)
    {
        $withoutPrefix = (bool) $withoutPrefix;

        $db = $this->getConnection();
        $prefix = $this->dbSettings['prefix'];
        $prefixLength = strlen($prefix);

        $column = $columnName = 'table_name';

        $input = array();

        if ($withoutPrefix && $prefixLength) {
            $column = sprintf('SUBSTRING(%1$s FROM 1 + CHAR_LENGTH(:name)) %1$s', $columnName);
            $input[':name'] = $prefix;
        }

        $condition = 'table_schema = database()';

        if ($prefixLength) {
            $escape = '=';
            $condition .= sprintf(" AND %s LIKE :like ESCAPE '%s'", $columnName, $escape);
            $input[':like'] = $this->quoteLike($prefix, $escape) . '%';
        }

        $query = sprintf('SELECT %s FROM information_schema.tables WHERE %s;', $column, $condition);
        $statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $result = $statement->execute($input);

        if (!$result) {
            // @codeCoverageIgnoreStart
            $this->throwRuntimeException(
                $statement,
                sprintf('Failed to obtain tables from database: %s', var_export($query, true))
            );
        } // @codeCoverageIgnoreEnd

        $result = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

        return $result;
    }

    /**
     * throw a runtime exception and provide error info for the statement if available
     *
     * @param PDOStatement $statement
     * @param string $message
     *
     * @throws RuntimeException
     */
    private function throwRuntimeException(PDOStatement $statement, $message = '')
    {
        $reason = $statement->errorInfo()
            ? vsprintf('SQLSTATE[%s]: %s: %s', $statement->errorInfo())
            : 'no error info for statement';

        if (strlen($message)) {
            $message .= ': ';
        } else {
            $message = '';
        }

        throw new RuntimeException($message . $reason);
    }

    /**
     * quote a string so that it is safe to use in a LIKE
     *
     * @param string $string
     * @param string $escape character - single us-ascii character
     *
     * @return string
     */
    private function quoteLike($string, $escape = '=')
    {
        $translation = array(
            $escape => $escape . $escape,
            '%'     => $escape . '%',
            '_'     => $escape . '_',
        );

        return strtr($string, $translation);
    }

    /**
     * Get list of db tables status
     *
     * @param bool $withoutPrefix
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getTablesStatus($withoutPrefix = false)
    {
        $db = $this->getConnection();
        $prefix = $this->dbSettings['prefix'];
        if (strlen($prefix) > 0) {
            $statement = $db->prepare('SHOW TABLE STATUS LIKE :like', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $statement->execute(
                array(':like' => $prefix . '%')
            );
        } else {
            $statement = $db->query('SHOW TABLE STATUS');
        }

        if ($statement) {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $return = array();
            foreach ($result as $table) {
                if (true === $withoutPrefix) {
                    $table['Name'] = str_replace($prefix, '', $table['Name']);
                }
                $return[$table['Name']] = $table;
            }

            return $return;
        }

        return array();
    }

    /**
     * @return array
     */
    public function getDbSettings()
    {
        return $this->dbSettings;
    }

    /**
     * @return boolean
     */
    public function getIsSocketConnect()
    {
        return $this->isSocketConnect;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'database';
    }

    /**
     * @param OutputInterface $output
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function dropDatabase(OutputInterface $output)
    {
        $this->detectDbSettings($output);
        $db = $this->getConnection();
        $db->query('DROP DATABASE `' . $this->dbSettings['dbname'] . '`');
        $output->writeln('<info>Dropped database</info> <comment>' . $this->dbSettings['dbname'] . '</comment>');
    }

    /**
     * @param OutputInterface $output
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function dropTables(OutputInterface $output)
    {
        $result = $this->getTables();
        $query = 'SET FOREIGN_KEY_CHECKS = 0; ';
        $count = 0;
        foreach ($result as $tableName) {
            $query .= 'DROP TABLE IF EXISTS `' . $tableName . '`; ';
            $count++;
        }
        $query .= 'SET FOREIGN_KEY_CHECKS = 1;';
        $this->getConnection()->query($query);
        $output->writeln('<info>Dropped database tables</info> <comment>' . $count . ' tables dropped</comment>');
    }

    /**
     * @param OutputInterface $output
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDatabase(OutputInterface $output)
    {
        $this->detectDbSettings($output);
        $db = $this->getConnection();
        $db->query('CREATE DATABASE IF NOT EXISTS `' . $this->dbSettings['dbname'] . '`');
        $output->writeln('<info>Created database</info> <comment>' . $this->dbSettings['dbname'] . '</comment>');
    }

    /**
     * @param string $command example: 'VARIABLES', 'STATUS'
     * @param string|null $variable [optional]
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function runShowCommand($command, $variable = null)
    {
        $db = $this->getConnection();

        if (null !== $variable) {
            $statement = $db->prepare(
                'SHOW /*!50000 GLOBAL */ ' . $command . ' LIKE :like',
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );
            $statement->execute(
                array(':like' => $variable)
            );
        } else {
            $statement = $db->query('SHOW /*!50000 GLOBAL */ ' . $command);
        }

        if ($statement) {
            /** @var array|string[] $result */
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $return = array();
            foreach ($result as $row) {
                $return[$row['Variable_name']] = $row['Value'];
            }

            return $return;
        }

        return array();
    }

    /**
     * @param string|null $variable [optional]
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getGlobalVariables($variable = null)
    {
        return $this->runShowCommand('VARIABLES', $variable);
    }

    /**
     * @param string|null $variable [optional]
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getGlobalStatus($variable = null)
    {
        return $this->runShowCommand('STATUS', $variable);
    }

    /**
     * small helper method to obtain an object of type OutputInterface
     *
     * @param OutputInterface|null $output
     *
     * @return OutputInterface
     */
    private function fallbackOutput(OutputInterface $output = null)
    {
        if (null !== $output) {
            return $output;
        }

        if ($this->getHelperSet()->has('io')) {
            /** @var $helper IoHelper */
            $helper = $this->getHelperSet()->get('io');
            $output = $helper->getOutput();
        }

        if (null === $output) {
            $output = new NullOutput();
        }

        return $output;
    }
}
