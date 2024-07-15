<?php

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
