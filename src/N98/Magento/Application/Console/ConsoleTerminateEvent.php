<?php

namespace N98\Magento\Application\Console;

use Symfony\Component\Console\Event\ConsoleTerminateEvent as BaseConsoleTerminateEvent;

/**
 * Class ConsoleTerminateEvent
 * @package N98\Magento\Application\Console
 */
class ConsoleTerminateEvent extends BaseConsoleTerminateEvent
{
    use SymfonyCompatibilityTrait;
}
