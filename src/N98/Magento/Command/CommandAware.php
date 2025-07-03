<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command;

use Symfony\Component\Console\Command\Command;

/**
 * Interface CommandAware
 * @package N98\Magento\Command
 */
interface CommandAware
{
    /**
     * @param Command $command
     * @return void
     */
    public function setCommand(Command $command);
}
