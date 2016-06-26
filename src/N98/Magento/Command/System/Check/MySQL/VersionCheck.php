<?php

namespace N98\Magento\Command\System\Check\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;

class VersionCheck implements SimpleCheck
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results)
    {
        $result = $results->createResult();

        $dbAdapter = $this->resource->getConnection();

        if (!$dbAdapter instanceof AdapterInterface) {
            $result->setStatus(Result::STATUS_ERROR);
            $result->setMessage(
                "<error>Mysql Version: Can not check. Unable to obtain resource connection 'core_write'.</error>"
            );
            return;
        }

        /**
         * Check Version
         */
        $mysqlVersion = $dbAdapter->fetchOne('SELECT VERSION()');
        if (version_compare($mysqlVersion, '4.1.20', '>=')) {
            $result->setStatus(Result::STATUS_OK);
            $result->setMessage("<info>MySQL Version <comment>$mysqlVersion</comment> found.</info>");
        } else {
            $result->setStatus(Result::STATUS_ERROR);
            $result->setMessage("<error>MySQL Version $mysqlVersion found. Upgrade your MySQL Version.</error>");
        }
    }
}
