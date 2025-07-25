<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\MySQL;

use Magento\Framework\App\ResourceConnection;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;

/**
 * Class EnginesCheck
 * @package N98\Magento\Command\System\Check\MySQL
 */
class EnginesCheck implements SimpleCheck
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

        $engines = $dbAdapter->fetchAll('SHOW ENGINES');
        $innodbFound = false;
        foreach ($engines as $engine) {
            if (strtolower($engine['Engine']) == 'innodb') {
                $innodbFound = true;
                break;
            }
        }

        $result->setStatus($innodbFound ? Result::STATUS_OK : Result::STATUS_ERROR);

        if ($innodbFound) {
            $result->setMessage('<info>Required MySQL Storage Engine <comment>InnoDB</comment> found.</info>');
        } else {
            $result->setMessage('<error>Required MySQL Storage Engine "InnoDB" not found!</error>');
        }
    }
}
