<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util;

/**
 * ProjectComposer is a util class to access information of project composer packages
 */
class ProjectComposer
{
    /**
     * @var string
     */
    private $magentoRootFolder;

    /**
     * @param string $magentoRootFolder
     */
    public function __construct(string $magentoRootFolder)
    {
        $this->magentoRootFolder = $magentoRootFolder;
    }

    /**
     * @return bool
     */
    public function isLockFile()
    {
        $composerJson = $this->getComposerLockPath();

        return file_exists($composerJson);
    }

    public function isComposerJsonFile()
    {
        $composerJson = $this->magentoRootFolder . '/composer.json';

        return file_exists($composerJson);
    }

    /**
     * Returns an array with all composer packages
     * @return array
     * @throws \JsonException
     */
    public function getComposerLockPackages()
    {
        try {
            $composerLockContent = json_decode(
                file_get_contents($this->getComposerLockPath()),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            return [];
        }

        $requiredPackages = [];
        $requiredDevPackages = [];

        if (isset($composerLockContent['packages'])) {
            $packageNames = array_column($composerLockContent['packages'], 'name');

            $requiredPackages = array_combine($packageNames, $composerLockContent['packages']);
        }

        if (isset($composerLockContent['packages-dev'])) {
            $packageNames = array_column($composerLockContent['packages-dev'], 'name');

            $requiredDevPackages = array_combine($packageNames, $composerLockContent['packages-dev']);
        }

        return array_merge($requiredPackages, $requiredDevPackages);
    }

    /**
     * @return string
     */
    private function getComposerLockPath(): string
    {
        return $this->magentoRootFolder . '/composer.lock';
    }
}
