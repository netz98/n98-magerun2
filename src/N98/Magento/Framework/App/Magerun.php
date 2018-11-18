<?php

namespace N98\Magento\Framework\App;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use RuntimeException;

class Magerun implements \Magento\Framework\AppInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Launch application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        /*
         * this method is intentionally left empty
         *
         * earlier the area-code was set to "adminhtml" but this should be done
         * within a Command::execute() implementation if the command needs a specific
         * area.
         *
         * this might even extend to bootstrapping the application as it configures
         * the ObjectManager:
         *
         * @see \N98\Magento\Application::_initMagento2()
         */
        return null;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if ($this->objectManager === null) {
            throw new RuntimeException('Please initialize Magento to use the object manager.');
        }

        return $this->objectManager;
    }

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
