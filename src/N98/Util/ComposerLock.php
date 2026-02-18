<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util;

use Traversable;

class ComposerLock implements \IteratorAggregate
{
    /**
     * @var object
     */
    private $composerJsonData;

    /**
     * @var string
     */
    private $directoryOfComposerFile;

    /**
     * @param string $directoryOfComposerFile
     */
    public function __construct($directoryOfComposerFile)
    {
        $this->directoryOfComposerFile = $directoryOfComposerFile;
    }

    private function load()
    {
        if (!empty($this->composerJsonData)) {
            return;
        }

        if (file_exists($this->directoryOfComposerFile . '/composer.lock')) {
            $this->composerJsonData = json_decode(
                file_get_contents($this->directoryOfComposerFile . '/composer.lock'),
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        } else {
            $this->composerJsonData = [];
        }
    }

    public function getData()
    {
        $this->load();

        return $this->composerJsonData;
    }

    public function getPackages(): array
    {
        $this->load();

        $packages = [];

        if (isset($this->composerJsonData->packages)) {
            $packages = $this->composerJsonData->packages;
        }

        if (isset($this->composerJsonData->{'packages-dev'})) {
            $packages = array_merge($packages, $this->composerJsonData->{'packages-dev'});
        }

        return $packages;
    }

    public function getPackageByName(string $packageName)
    {
        foreach ($this->getPackages() as $package) {
            if ($package->name === $packageName) {
                return $package;
            }
        }

        return null;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->getData());
    }
}
