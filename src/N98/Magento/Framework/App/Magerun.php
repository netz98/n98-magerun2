<?php

namespace N98\Magento\Framework\App;

use Magento\Framework\App;

class Magerun implements \Magento\Framework\AppInterface
{
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        //var_dump($objectManager);
    }

    /**
     * Launch application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        // TODO: Implement launch() method.
    }

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        // TODO: Implement catchException() method.
    }
}