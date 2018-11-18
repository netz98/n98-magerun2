<?php

namespace N98\Util\Console\Helper;

use ArrayIterator;
use CallbackFilterIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use N98\Magento\Application;
use N98\Magento\Application\DetectionResultInterface;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use UnexpectedValueException;

/**
 * Class MagentoHelper
 *
 * @package N98\Util\Console\Helper
 */
class MagentoHelper extends AbstractHelper implements DetectionResultInterface
{
    /**
     * @var string
     */
    protected $_magentoRootFolder = null;

    /**
     * @var int
     */
    protected $_magentoMajorVersion = Application::MAGENTO_MAJOR_VERSION_1;

    /**
     * @var bool
     */
    protected $_magentoEnterprise = false;

    /**
     * @var bool
     */
    protected $_magerunStopFileFound = false;

    /**
     * @var string
     */
    protected $_magerunStopFileFolder = null;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $baseConfig = array();

    /**
     * @var string
     */
    protected $_customConfigFilename = 'n98-magerun2.yaml';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'magento';
    }

    /**
     * Start Magento detection
     *
     * @param string $folder
     * @param array $subFolders [optional] sub-folders to check
     * @return bool
     */
    public function detect($folder, array $subFolders = array())
    {
        $folders = $this->splitPathFolders($folder);
        $folders = $this->checkMagerunFile($folders);
        $folders = $this->checkModman($folders);
        $folders = array_merge($folders, $subFolders);

        foreach (array_reverse($folders) as $searchFolder) {
            if (!is_dir($searchFolder) || !is_readable($searchFolder)) {
                continue;
            }

            $found = $this->_search($searchFolder);
            if ($found) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $folder
     *
     * @return array
     */
    protected function splitPathFolders($folder)
    {
        $folders = array();

        $folderParts = explode('/', $folder);
        foreach ($folderParts as $key => $part) {
            $explodedFolder = implode('/', array_slice($folderParts, 0, $key + 1));
            if ($explodedFolder !== '') {
                $folders[] = $explodedFolder;
            }
        }

        return $folders;
    }

    /**
     * Check for magerun stop-file
     *
     * @param array $folders
     *
     * @return array
     */
    protected function checkMagerunFile(array $folders)
    {
        $stopFile = '.' . pathinfo($this->_customConfigFilename, PATHINFO_FILENAME);

        foreach ($this->searchFolders($folders) as $searchFolder) {
            $magerunFilePath = $searchFolder . '/' . $stopFile;
            if (is_link($magerunFilePath) && !file_exists($magerunFilePath)) {
                throw new \RuntimeException(
                    sprintf("Stopfile is broken symlink: '%s'", $magerunFilePath),
                    2
                );
            }
            if (!is_readable($magerunFilePath) || !is_file($magerunFilePath)) {
                continue;
            }
            $this->_magerunStopFileFound = true;
            $this->_magerunStopFileFolder = $searchFolder;
            $magerunFileContent = trim(file_get_contents($magerunFilePath));
            $message = sprintf(
                'Found stopfile \'%s\' file with content <info>%s</info> from \'%s\'',
                $stopFile,
                $magerunFileContent,
                $searchFolder
            );
            $this->writeDebug($message);

            array_push($folders, $searchFolder . '/' . $magerunFileContent);
            break;
        }

        return $folders;
    }

    /**
     * Turn an array of folders into a Traversable of readable paths.
     *
     * @param array $folders
     * @return CallbackFilterIterator Traversable of strings that are readable paths
     */
    private function searchFolders(array $folders)
    {
        $that = $this;

        $callback = function ($searchFolder) use ($that) {
            if (!is_readable($searchFolder)) {
                $that->writeDebug('Folder <info>' . $searchFolder . '</info> is not readable. Skip.');

                return false;
            }

            return true;
        };

        return new CallbackFilterIterator(
            new ArrayIterator(array_reverse($folders)),
            $callback
        );
    }

    /**
     * @param string $message
     * @return void
     */
    private function writeDebug($message)
    {
        if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
            $this->output->writeln(
                '<debug>' . $message . '</debug>'
            );
        }
    }

    /**
     * Check for modman file and .basedir
     *
     * @param array $folders
     *
     * @return array
     */
    protected function checkModman(array $folders)
    {
        foreach ($this->searchFolders($folders) as $searchFolder) {
            $finder = Finder::create();
            $finder
                ->files()
                ->ignoreUnreadableDirs(true)
                ->depth(0)
                ->followLinks()
                ->ignoreDotFiles(false)
                ->name('.basedir')
                ->in($searchFolder);

            $count = $finder->count();
            if ($count > 0) {
                $baseFolderContent = trim(file_get_contents($searchFolder . '/.basedir'));
                $this->writeDebug('Found modman .basedir file with content <info>' . $baseFolderContent . '</info>');

                if (!empty($baseFolderContent)) {
                    array_push($folders, $searchFolder . '/../' . $baseFolderContent);
                }
            }
        }

        return $folders;
    }

    /**
     * @param string $searchFolder
     *
     * @return bool
     */
    protected function _search($searchFolder)
    {
        $this->writeDebug('Search for Magento in folder <info>' . $searchFolder . '</info>');

        if (!is_dir($searchFolder . '/app')) {
            return false;
        }

        $finder = Finder::create();
        $finder
            ->ignoreUnreadableDirs(true)
            ->depth(0)
            ->followLinks()
            ->name('Mage.php')
            ->name('bootstrap.php')
            ->name('autoload.php')
            ->in($searchFolder . '/app');

        if ($finder->count() > 0) {
            $files = iterator_to_array($finder, false);
            /* @var $file \SplFileInfo */

            $hasMageFile = false;
            foreach ($files as $file) {
                if ($file->getFilename() == 'Mage.php') {
                    $hasMageFile = true;
                }
            }

            $this->_magentoRootFolder = $searchFolder;

            // Magento 2 does not have a god class and thus if this file is not there it is version 2
            if ($hasMageFile == false) {
                $this->_magentoMajorVersion = Application::MAGENTO_MAJOR_VERSION_2;
            } else {
                $this->_magentoMajorVersion = Application::MAGENTO_MAJOR_VERSION_1;
            }

            $this->writeDebug(
                sprintf(
                    'Found Magento <info> v%d </info> in folder <info>%s</info>',
                    $this->_magentoMajorVersion,
                    $this->_magentoRootFolder
                )
            );

            return true;
        }

        return false;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getRootFolder()
    {
        return $this->_magentoRootFolder;
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isEnterpriseEdition()
    {
        return $this->_magentoEnterprise;
    }

    /**
     * @api
     *
     * @return int
     */
    public function getMajorVersion()
    {
        return $this->_magentoMajorVersion;
    }

    /**
     * @api
     *
     * @return boolean
     */
    public function isMagerunStopFileFound()
    {
        return $this->_magerunStopFileFound;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getMagerunStopFileFolder()
    {
        return $this->_magerunStopFileFolder;
    }

    /**
     * @api
     *
     * @return array
     * @throws RuntimeException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getBaseConfig()
    {
        if (!$this->baseConfig) {
            $this->initBaseConfig();
        }

        return $this->baseConfig;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    private function initBaseConfig()
    {
        $this->baseConfig = [];

        $application = $this->getApplication();
        $application->detectMagento(null, $this->output);
        $application->initMagento();
        /** @var DirectoryList $directoryList */
        $directoryList = $application->getObjectManager()->get(DirectoryList::class);
        $configDir = rtrim($directoryList->getPath(DirectoryList::CONFIG), DIRECTORY_SEPARATOR);

        $configFiles = [
            $configDir . '/config.php',
            $configDir . '/env.php',
        ];

        foreach ($configFiles as $configFile) {
            $this->addBaseConfig($configFile);
        }
    }

    /**
     * private getter for application that has magento detected
     *
     * @return Application
     * @throws \Exception
     */
    private function getApplication()
    {
        $command = $this->getHelperSet()->getCommand();

        $application = $command ? $command->getApplication() : new Application();

        // verify type because of detectMagento() call below
        if (!$application instanceof Application) {
            throw new UnexpectedValueException(
                sprintf('Expected magerun application got %s', get_class($application))
            );
        }

        $application->detectMagento();

        return $application;
    }

    /**
     * @param string $configFile
     */
    private function addBaseConfig($configFile)
    {
        if (!(is_file($configFile) && is_readable($configFile))) {
            throw new RuntimeException(sprintf('%s is not readable', $configFile));
        }

        $config = @include $configFile;

        if (!is_array($config)) {
            throw new RuntimeException(sprintf('%s is corrupted. Please check it.', $configFile));
        }

        $this->baseConfig = array_merge($this->baseConfig, $config);
    }
}
