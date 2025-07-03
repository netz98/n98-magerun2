<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check;

use Traversable;

/**
 * Class ResultCollection
 *
 * @package N98\Magento\Command\System\Check
 */
class ResultCollection implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $_results;

    /**
     * @var string
     */
    protected $_resultGroup;

    /**
     * Key/Value registry for checks
     *
     * @var array
     */
    protected $registry;

    /**
     * Register value in registry
     *
     * @param string $key
     * @param mixed $value
     */
    public function setRegistryValue($key, $value)
    {
        $this->registry[$key] = $value;
    }

    /**
     * Check if key exists
     *
     * @param $key
     * @return bool
     */
    public function hasRegistryKey($key): bool
    {
        return isset($this->registry[$key]);
    }

    /**
     * Return a registry value
     *
     * @param string $key
     * @return mixed
     */
    public function getRegistryValue($key)
    {
        return $this->registry[$key];
    }

    /**
     * @param Result $result
     * @return $this
     */
    public function addResult(Result $result)
    {
        $this->_results[$result->getResultGroup()][] = $result;

        return $this;
    }

    /**
     * @param string $status
     * @param string $message
     * @return Result
     */
    public function createResult($status = Result::STATUS_OK, $message = '')
    {
        $result = new Result($status, $message);
        $result->setResultGroup($this->_resultGroup);
        $this->addResult($result);

        return $result;
    }

    /**
     * @param string $resultGroup
     */
    public function setResultGroup($resultGroup)
    {
        $this->_resultGroup = $resultGroup;
    }

    /**
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     */
    public function getIterator(): Traversable
    {
        $filteredResults = [];

        foreach ($this->_results as $resultGroup => $groupResults) {
            foreach ($groupResults as $result) {
                if (!$result->isSkipped()) {
                    $filteredResults[$resultGroup][] = $result;
                }
            }
        }

        return new \ArrayObject($filteredResults);
    }
}
