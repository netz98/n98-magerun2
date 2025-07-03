<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\System\Check\Hyva;

trait HyvaTrait
{
    /**
     * Check if Hyva main package is installed
     *
     * @param array $projectComposerPackages
     * @param array $commandConfig
     * @return bool
     */
    public function isHyvaAvailable(array $projectComposerPackages, array $commandConfig): bool
    {
        $mainPackage = $commandConfig['hyva']['main-package'];
        return isset($projectComposerPackages[$mainPackage]);
    }
}
