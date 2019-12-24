<?php

namespace N98\Magento\Command\Script\Repository;

use N98\Util\OperatingSystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ScriptLoader
 * @package N98\Magento\Command\Script\Repository
 */
final class ScriptLoader
{
    const LOCATION_PROJECT = 'project';
    const LOCATION_PERSONAL = 'personal';
    const LOCATION_MODULE = 'module';
    const LOCATION_SYSTEM = 'system';

    const BASENAME_MODULES = 'modules';
    const BASENAME_SCRIPTS = 'scripts';

    /**
     * @var string basename, e.g. "n98-magerun2"
     */
    private $basename;

    /**
     * @var string magento root folder
     */
    private $magentoRootFolder;

    /**
     * @var array collection of folders, key is the folder (normalized), value is the type of it
     */
    private $folders;

    /**
     * @var array
     */
    private $scriptFolders;

    /**
     * @var array
     */
    private $scriptFiles;

    /**
     * @param array $scriptFolders provided from the config file (config: script.folders, see YAML for details)
     * @param string $basename
     * @param string $magentoRootFolder
     */
    public function __construct(array $scriptFolders, $basename, $magentoRootFolder)
    {
        $this->basename = $basename;
        $this->magentoRootFolder = $magentoRootFolder;
        $this->scriptFolders = $scriptFolders;
    }

    /**
     * initialize folders by type. folders is the main concept of this class
     */
    private function init()
    {
        // add Magento root folder
        $this->addFolder(self::LOCATION_PROJECT, $this->magentoRootFolder);

        // add home-dir folder
        $this->addPersonalFolder();

        // add Magerun config folders
        $this->addFolders(self::LOCATION_SYSTEM, $this->scriptFolders);

        // remove folders again which do not exist
        $this->filterInvalidFolders();

        // finally find all *.magerun scripts in so far initialized folders
        $this->scriptFiles = $this->findScriptFiles();
    }

    /**
     * @param string $location
     * @param string $path
     */
    private function addFolder($location, $path)
    {
        $normalized = rtrim($path, '/');
        $this->folders[$normalized] = $location;
    }

    /**
     * Add home-dir folder(s), these are multiple on windows due to backwards compatibility
     */
    private function addPersonalFolder()
    {
        $basename = $this->basename;
        $homeDir = OperatingSystem::getHomeDir();

        if (false !== $homeDir) {
            if (OperatingSystem::isWindows()) {
                $this->addFolder(self::LOCATION_PERSONAL, $homeDir . '/' . $basename . '/' . self::BASENAME_SCRIPTS);
            }
            $this->addFolder(self::LOCATION_PERSONAL, $homeDir . '/.' . $basename . '/' . self::BASENAME_SCRIPTS);
        }
    }

    /**
     * @param string $location
     * @param array $paths
     */
    private function addFolders($location, array $paths)
    {
        foreach ($paths as $path) {
            $this->addFolder($location, $path);
        }
    }

    private function filterInvalidFolders()
    {
        foreach ($this->folders as $path => $type) {
            if (!is_dir($path)) {
                unset($this->folders[$path]);
            }
        }
    }

    private function getFolderPaths()
    {
        return array_keys($this->folders);
    }

    /**
     * @return array
     */
    private function findScriptFiles()
    {
        $scriptFiles = [];

        $folders = $this->getFolderPaths();

        if (!$folders) {
            return $scriptFiles;
        }

        $finder = $this->createScriptFilesFinder($folders);

        foreach ($finder as $file) {
            $filename = $file->getFilename();
            $scriptFiles[$filename] = $this->createScriptFile($file);
        }

        ksort($scriptFiles, SORT_STRING);

        return $scriptFiles;
    }

    /**
     * @param array $folders to search for magerun scripts in
     * @return Finder|\Symfony\Component\Finder\SplFileInfo[]
     */
    private function createScriptFilesFinder(array $folders)
    {
        /* @var $finder Finder */
        $finder = Finder::create()
            ->files()
            ->exclude('pub')
            ->followLinks()
            ->ignoreUnreadableDirs(true)
            ->name('*' . AbstractRepositoryCommand::MAGERUN_EXTENSION)
            ->in($folders);

        return $finder;
    }

    private function createScriptFile(SplFileInfo $file)
    {
        $pathname = $file->getPathname();

        $scriptFile = [
            'fileinfo'    => $file,
            'description' => $this->readDescriptionFromFile($pathname),
            'location'    => $this->getLocation($pathname),
        ];

        return $scriptFile;
    }

    /**
     * Reads the first line. If it's a comment return it.
     *
     * @param string $file
     *
     * @return string comment text or zero-length string if no comment was given
     */
    private function readDescriptionFromFile($file)
    {
        $line = $this->readFirstLineOfFile($file);
        if (null === $line) {
            return '';
        }

        if (isset($line[0]) && $line[0] != '#') {
            return '';
        }

        return trim(substr($line, 1));
    }

    /**
     * Read first line of a file w/o line-separators and end-of-line whitespace
     *
     * @param string $file
     *
     * @return string|null first line or null, if it was not possible to obtain a first line
     */
    private function readFirstLineOfFile($file)
    {
        $handle = @fopen($file, 'r');
        if (!$handle) {
            return null;
        }

        $buffer = fgets($handle);
        fclose($handle);

        if (false === $file) {
            return null;
        }

        return rtrim($buffer);
    }

    /**
     * @param string $pathname
     *
     * @return string
     */
    private function getLocation($pathname)
    {
        if (null !== $location = $this->detectLocationViaFolderByPathname($pathname)) {
            return $location;
        }

        if (null !== $location = $this->detecLocationModuleByPathname($pathname)) {
            return $location;
        }

        return self::LOCATION_SYSTEM;
    }

    /**
     * private helper function to detect type by pathname with the help of initialized folders
     *
     * @see init()
     *
     * @param string $pathname
     * @return mixed|null
     */
    private function detectLocationViaFolderByPathname($pathname)
    {
        foreach ($this->folders as $path => $type) {
            if (0 === strpos($pathname, $path . '/')) {
                return $type;
            }
        }

        return null;
    }

    /**
     * private helper function to detect if a script is from within a module
     *
     * @param string $pathname
     * @return null|string
     */
    private function detecLocationModuleByPathname($pathname)
    {
        $pos = strpos($pathname, '/' . $this->basename . '/' . self::BASENAME_MODULES . '/');
        if (false !== $pos) {
            return $this::LOCATION_MODULE;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        if (null === $this->scriptFiles) {
            $this->init();
        }

        return $this->scriptFiles;
    }
}
