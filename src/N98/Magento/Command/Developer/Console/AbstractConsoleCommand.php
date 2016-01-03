<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\ObjectManager;
use Psy\Command\ReflectingCommand;

abstract class AbstractConsoleCommand extends ReflectingCommand
{
    /**
     * @param string $variable
     * @param mixed $value
     *
     * @return void
     */
    protected function setScopeVariable($variable, $value)
    {
        $variables = $this->context->getAll();
        $variables[$variable] = $value;

        $this->context->setAll($variables);
    }

    /**
     * @param string $type
     * @return mixed
     */
    protected function get($type)
    {
        $di = $this->getScopeVariable('di');

        /** @var $di ObjectManager */
        return $di->get($type);
    }

    /**
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    protected function create($type, $arguments = [])
    {
        $di = $this->getScopeVariable('di');

        /** @var $di ObjectManager */
        return $di->create($type, $arguments);
    }
}
