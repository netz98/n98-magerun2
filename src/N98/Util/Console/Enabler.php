<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console;

use N98\Util\OperatingSystem;
use RuntimeException;
use Symfony\Component\Console\Command\Command;

/**
 * Class Enabler
 *
 * Utility class to check console command requirements to be "enabled".
 *
 * @see \N98\Magento\Command\Database\DumpCommand::execute()
 *
 * @package N98\Util\Console
 */
class Enabler
{
    /**
     * @var Command
     */
    private $command;

    /**
     * Enabler constructor.
     * @param \Symfony\Component\Console\Command\Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * @param $name
     *
     * @return void
     */
    public function functionExists($name)
    {
        $this->assert(function_exists($name), sprintf('function "%s" is not available', $name));
    }

    /**
     * @return void
     */
    public function operatingSystemIsNotWindows()
    {
        $this->assert(!OperatingSystem::isWindows(), 'operating system is windows');
    }

    /**
     * @param $condition
     * @param $message
     */
    private function assert($condition, $message)
    {
        if ($condition) {
            return;
        }

        throw new RuntimeException(
            sprintf('Command %s is not available because %s.', $this->command->getName(), $message)
        );
    }
}
