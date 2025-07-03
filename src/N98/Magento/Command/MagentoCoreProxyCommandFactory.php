<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command;

use Symfony\Component\Console\Command\Command;

class MagentoCoreProxyCommandFactory
{
    /**
     * @param string $magentoRootDir
     * @param string $commandName
     * @param array $usage
     * @param string $description
     * @param string $help
     * @param array $definition
     * @return Command
     */
    public function create(
        string $magentoRootDir,
        string $commandName,
        array  $usage,
        string $description,
        string $help,
        array  $definition
    ) {
        return new MagentoCoreProxyCommand(
            $magentoRootDir,
            $commandName,
            $usage,
            $description,
            $help,
            $definition
        );
    }
}
