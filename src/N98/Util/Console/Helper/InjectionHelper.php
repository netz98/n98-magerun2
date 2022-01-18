<?php

namespace N98\Util\Console\Helper;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;

/**
 * Class InjectionHelper
 * @package N98\Util\Console\Helper
 */
class InjectionHelper extends AbstractHelper
{
    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'injection';
    }

    /**
     * @param \Magento\Framework\ObjectManagerInterface $object
     * @param string $methodName
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @throws \ReflectionException
     */
    public function methodInjection($object, $methodName, ObjectManagerInterface $objectManager)
    {
        $parameters = $this->getMethod($object, $methodName);
        $argumentsToInject = array_map([$objectManager, 'get'], $parameters);

        call_user_func_array([$object, $methodName], $argumentsToInject);
    }

    /**
     * @param string $class
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     *
     * @return object
     * @throws \ReflectionException
     */
    public function constructorInjection($class, ObjectManagerInterface $objectManager)
    {
        $parameters = $this->getMethod($class, '__construct');
        $argumentsToInject = array_map([$objectManager, 'get'], $parameters);

        return (new \ReflectionClass($class))->newInstanceArgs($argumentsToInject);
    }

    /**
     * Read class method signature
     *
     * @param string $class
     * @param string $methodName
     * @return array
     * @throws \ReflectionException
     */
    protected function getMethod($class, $methodName)
    {
        $refl = new \ReflectionClass($class);
        if (!$refl->hasMethod($methodName)) {
            return [];
        }

        $method = $refl->getMethod($methodName);
        if (!$method) {
            throw new \InvalidArgumentException(
                sprintf('Unable to obtain method "%s" for class "%s"', $class, $methodName)
            );
        }

        return array_map([$this, 'getParameterClass'], $method->getParameters());
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return null|string
     * @throws \ReflectionException
     */
    private function getParameterClass(\ReflectionParameter $parameter)
    {
        $class = $parameter->getType() && !$parameter->getType()->isBuiltin()
            ? (new \ReflectionClass($parameter->getType()->getName()))->getName()
            : null;

        return $class;
    }
}
