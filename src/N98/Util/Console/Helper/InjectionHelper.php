<?php

namespace N98\Util\Console\Helper;

use Magento\Framework\ObjectManager\ObjectManager;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;

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
     * @param Object $object
     * @param string $methodName
     */
    public function methodInjection($object, $methodName, ObjectManager $objectManager)
    {
        $parameters = $this->getMethod($object, $methodName);
        $argumentsToInject = array_map([$objectManager, 'get'], $parameters);

        call_user_func_array([$object, $methodName], $argumentsToInject);
    }

    /**
     * @param string $class
     * @param ObjectManager $objectManager
     *
     * @return object
     */
    public function constructorInjection($class, ObjectManager $objectManager)
    {
        $parameters = $this->getMethod($class, '__construct');
        $argumentsToInject = array_map([$objectManager, 'get'], $parameters);

        $refl = new \ReflectionClass($class);
        $object = $refl->newInstanceArgs($argumentsToInject);

        return $object;
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
                sprintf("Unable to obtain method \"%s\" for class \"%s\"", $class, $methodName)
            );
        }

        $result = array_map([$this, 'getParameterClass'], $method->getParameters());

        return $result;
    }

    private function getParameterClass(\ReflectionParameter $parameter)
    {
        return $parameter->getClass() !== null ? $parameter->getClass()->getName() : null;
    }
}
