<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\System\Check;

use N98\Util\ProjectComposer;

trait ProjectComposerTrait
{
    /**
     * @param \N98\Magento\Command\System\Check\ResultCollection $results
     * @param string $magentoRootFolder
     * @return array
     * @throws \JsonException
     */
    public function getProjectComposerPackages(ResultCollection $results, $magentoRootFolder)
    {
        if ($results->hasRegistryKey('project_composer_packages')) {
            $projectComposerPackages = $results->getRegistryValue('project_composer_packages');
        } else {
            $composerUtil = new ProjectComposer($magentoRootFolder);
            $projectComposerPackages = $composerUtil->getComposerLockPackages();
        }

        return $projectComposerPackages;
    }
}
