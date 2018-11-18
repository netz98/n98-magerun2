<?php

namespace N98\Magento\Application;

use N98\Util\Console\Helper\MagentoHelper;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MagentoDetector
 * @package N98\Magento\Application
 */
class MagentoDetector
{
    /**
     * @param \Symfony\Component\Console\Input\InputInterface|null $input
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @param \N98\Magento\Application\Config $config
     * @param \Symfony\Component\Console\Helper\HelperSet $helperSet
     * @param string $magentoRootDirectory
     * @return \N98\Magento\Application\DetectionResult
     */
    public function detect(
        InputInterface $input,
        OutputInterface $output,
        Config $config,
        HelperSet $helperSet,
        $magentoRootDirectory = null
    ) {
        $input = $input ?: new ArgvInput();
        $output = $output ?: new ConsoleOutput();

        $folder = OperatingSystem::getCwd();

        if ($magentoRootDirectory) {
            $folder = $magentoRootDirectory;
        }

        if ($this->_checkRootDirOption($input)) {
            $subFolders = [$folder];
        } else {
            $subFolders = $config->getDetectSubFolders();
        }

        $helperSet->set(new MagentoHelper($input, $output), 'magento');
        /* @var $magentoHelper MagentoHelper */
        $magentoHelper = $helperSet->get('magento');

        return new DetectionResult($magentoHelper, $folder, $subFolders); // @TODO must be refactored
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    protected function _checkRootDirOption(InputInterface $input)
    {
        $rootDir = $input->getParameterOption('--root-dir');
        if (is_string($rootDir)) {
            $this->setRootDir($rootDir);

            return true;
        }

        return false;
    }

    /**
     * Set root dir (chdir()) of magento directory
     *
     * @param string $path to Magento directory
     */
    private function setRootDir($path)
    {
        if (isset($path[0]) && '~' === $path[0]) {
            $path = OperatingSystem::getHomeDir() . substr($path, 1);
        }

        $folder = realpath($path);
        if (is_dir($folder)) {
            chdir($folder);
        }
    }
}
