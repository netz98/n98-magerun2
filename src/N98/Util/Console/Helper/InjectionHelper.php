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

        $argumentsToInject = [];
        foreach ($parameters as $parameter) {
            $argumentsToInject[] = $objectManager->get($parameter[1]);
        }

        call_user_func_array([$object, $methodName], $argumentsToInject);
    }

    /**
     * Read class constructor signature
     *
     * @param Object $object
     * @param string $methodName
     * @return array|null
     * @throws \ReflectionException
     */
    protected function getMethod($object, $methodName)
    {
        $object = new \ReflectionObject($object);
        $result = null;
        $method = $object->getMethod($methodName);
        if ($method) {
            $result = [];
            /** @var $parameter \ReflectionParameter */
            foreach ($method->getParameters() as $parameter) {
                try {
                    $result[] = [
                        $parameter->getName(),
                        $parameter->getClass() !== null ? $parameter->getClass()->getName() : null,
                        !$parameter->isOptional(),
                        $parameter->isOptional()
                            ? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null)
                            : null,
                    ];
                } catch (\ReflectionException $e) {
                    $message = $e->getMessage();
                    throw new \ReflectionException($message, 0, $e);
                }
            }
        }

        return $result;
    }
}
