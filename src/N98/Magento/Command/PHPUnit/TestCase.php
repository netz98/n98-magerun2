<?php

namespace N98\Magento\Command\PHPUnit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use N98\Magento\Application;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class TestCase
 *
 * @codeCoverageIgnore
 * @package N98\Magento\Command\PHPUnit
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application = null;

    /**
     * @var string|null
     */
    private $root;

    /**
     * getter for the magento root directory of the test-suite
     *
     * @see ApplicationTest::testExecute
     *
     * @return string
     */
    public function getTestMagentoRoot()
    {
        if ($this->root) {
            return $this->root;
        }

        $root = getenv('N98_MAGERUN2_TEST_MAGENTO_ROOT');
        if (empty($root)) {
            $stopfile = getcwd() . '/.n98-magerun2';
            if (is_readable($stopfile) && $buffer = rtrim(file_get_contents($stopfile))) {
                $root = $buffer;
            }
        }
        if (empty($root)) {
            $this->markTestSkipped(
                'Please specify environment variable N98_MAGERUN2_TEST_MAGENTO_ROOT with path to your test ' .
                'magento installation!'
            );
        }

        $this->root = realpath($root);
        return $this->root;
    }

    /**
     * @return Application|PHPUnit_Framework_MockObject_MockObject
     */
    public function getApplication()
    {
        if ($this->application === null) {
            $root = $this->getTestMagentoRoot();

            /** @var Application|PHPUnit_Framework_MockObject_MockObject $application */
            $application = $this->getMock('N98\Magento\Application', array('getMagentoRootFolder'));
            $loader = require __DIR__ . '/../../../../../vendor/autoload.php';
            $application->setAutoloader($loader);
            $application->expects($this->any())->method('getMagentoRootFolder')->will($this->returnValue($root));
            $application->init();
            $application->initMagento();

            $this->application = $application;
        }

        return $this->application;
    }

    /**
     * @return AdapterInterface
     */
    public function getDatabaseConnection()
    {
        $resource = $this->getApplication()->getObjectManager()->get(ResourceConnection::class);

        return $resource->getConnection('write');
    }
}
