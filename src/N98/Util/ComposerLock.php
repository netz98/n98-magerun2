<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
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
     * @param $directoryOfComposerFile
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

        if (isset($packagesConfig->{'packages-dev'})) {
            $packages = array_merge($packages, $this->composerJsonData->{'dev-packages'});
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
