<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Util\Console\Helper;

use Symfony\Component\Console\Command\Command;

trait CommandTrait
{
    protected Command $command;

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    protected function getCommand(): Command
    {
        return $this->command;
    }
}
