<?php

namespace N98\Magento\Command\Database;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Database\Compressor;
use N98\Magento\Command\Database\Compressor\AbstractCompressor;
use N98\Util\Console\Helper\DatabaseHelper;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractDatabaseCommand extends AbstractMagentoCommand
{
    /**
     * @var array
     */
    protected $dbSettings;

    /**
     * @var bool
     */
    protected $isSocketConnect = false;


    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function detectDbSettings(OutputInterface $output)
    {
        $database = $this->getDatabaseHelper();
        $database->detectDbSettings($output);
        $this->isSocketConnect = $database->getIsSocketConnect();
        $this->dbSettings = $database->getDbSettings();
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == '_connection') {
            return $this->getDatabaseHelper()->getConnection();
        }
    }

    /**
     * Generate help for compression
     *
     * @return string
     */
    protected function getCompressionHelp()
    {
        $messages = array();
        $messages[] = '';
        $messages[] = '<comment>Compression option</comment>';
        $messages[] = ' Supported compression: gzip';
        $messages[] = ' The gzip cli tool has to be installed.';
        $messages[] = ' Additionally, for data-to-csv option tar cli tool has to be installed too.';

        return implode(PHP_EOL, $messages);
    }

    /**
     * @param string $type
     * @return AbstractCompressor
     * @deprecated Since 1.1.12; use AbstractCompressor::create() instead
     */
    protected function getCompressor($type)
    {
        return AbstractCompressor::create($type);
    }

    /**
     * @return string
     *
     * @deprecated Please use database helper
     */
    protected function getMysqlClientToolConnectionString()
    {
        return $this->getDatabaseHelper()->getMysqlClientToolConnectionString();
    }

    /**
     * Creates a PDO DSN for the adapter from $this->_config settings.
     *
     * @see Zend_Db_Adapter_Pdo_Abstract
     * @return string
     *
     * @deprecated Please use database helper
     */
    protected function _dsn()
    {
        return $this->getDatabaseHelper()->dsn();
    }

    /**
     * @param array $excludes
     * @param array $definitions
     * @param array $resolved Which definitions where already resolved -> prevent endless loops
     *
     * @return array
     *
     * @deprecated Please use database helper
     *
     * @throws \Exception
     */
    protected function resolveTables(array $excludes, array $definitions, array $resolved = array())
    {
        return $this->getDatabaseHelper()->resolveTables($excludes, $definitions, $resolved);
    }

    /**
     * @return DatabaseHelper
     */
    protected function getDatabaseHelper()
    {
        return $this->getHelper('database');
    }
}
