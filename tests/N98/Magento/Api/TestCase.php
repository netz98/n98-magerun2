<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Api;

use N98\Magento\TestApplication;

/**
 * Class TestCase
 *
 * @codeCoverageIgnore
 * @package N98\Magento\Command\PHPUnit
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestApplication
     */
    private $testApplication;

    /**
     *
     * @param string $type
     * @return object of $type configured by Magento DI of the test-application
     */
    public function getObject($type)
    {
        $objectManager = $this->getTestApplication()->getApplication()->getObjectManager();
        $object = $objectManager->get($type);
        $this->assertInstanceOf($type, $object);

        return $object;
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
