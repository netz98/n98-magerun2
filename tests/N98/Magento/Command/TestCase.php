<?php

namespace N98\Magento\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use N98\Magento\Application;
use N98\Magento\TestApplication;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class TestCase
 *
 * @codeCoverageIgnore
 * @package N98\Magento\Command\PHPUnit
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestApplication
     */
    private $testApplication;

    /**
     * getter for the magento root directory of the test-suite
     *
     * @see ApplicationTest::testExecute
     *
     * @return string
     */
    public function getTestMagentoRoot()
    {
        return $this->getTestApplication()->getTestMagentoRoot();
    }

    /**
     * @return Application|PHPUnit_Framework_MockObject_MockObject
     */
    public function getApplication()
    {
        return $this->getTestApplication()->getApplication();
    }

    /**
     * @return AdapterInterface
     */
    public function getDatabaseConnection()
    {
        $resource = $this->getApplication()->getObjectManager()->get(ResourceConnection::class);

        return $resource->getConnection('write');
    }

    /**
     * @return TestApplication
     */
    private function getTestApplication()
    {
        if (null === $this->testApplication) {
            $this->testApplication = new TestApplication();
        }

        return $this->testApplication;
    }
}
