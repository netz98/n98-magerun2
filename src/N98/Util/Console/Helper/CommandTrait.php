<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console\Helper;

use Symfony\Component\Console\Command\Command;

trait CommandTrait
{
    protected ?Command $command = null;

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    protected function getCommand(): ?Command
    {
        return $this->command;
    }
}
