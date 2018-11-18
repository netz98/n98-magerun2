<?php

namespace N98\Magento\Application\Console;

/**
 * Class ConsoleExceptionEvent
 * @package N98\Magento\Application\Console
 */
class ConsoleExceptionEvent extends \Symfony\Component\Console\Event\ConsoleExceptionEvent
{
    use SymfonyCompatibilityTrait;
}
