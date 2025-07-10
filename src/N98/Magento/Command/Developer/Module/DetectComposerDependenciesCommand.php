<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Developer\Module;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Io\File;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\ComposerLock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DetectComposerDependenciesCommand extends AbstractMagentoCommand
{
    const COMPOSER_FILE_NOT_FOUND = 'COMPOSER_FILE_NOT_FOUND';

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var array
     */
    private $localInstalledPackagesFromLockFile;

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $this->setName('dev:module:detect-composer-dependencies')
            ->addArgument('path', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Path to modules')
            ->addOption('only-missing', null, InputOption::VALUE_NONE, 'Print only missing dependencies.')
            ->addOption('check', null, InputOption::VALUE_NONE, 'Return exit code 1 if dependencies are missing.')
            ->setDescription(
                'This command will search for any soft and hard dependencies '
                . 'for the Magento 2 modules in the given paths and will generate a list of ' .
                'recommended composer dependencies for all found modules.'
            );
    }

    /**
     * Inject dependencies
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function inject(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Main command entry point
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->initMagento();

        $magentoRootPath = $this->getApplication()->getMagentoRootFolder();

        $this->loadProjectComposerPackagesByLockFile($magentoRootPath);

        $projectPsr4Namespaces = include $magentoRootPath . '/vendor/composer/autoload_psr4.php';

        $foundModules = $this->getModulesInModulePath($input->getArgument('path'));

        $totalMissing = 0;
        foreach ($foundModules as $foundModuleName => $foundModulePath) {
            $this->writeSection($output, 'Module: ' . $foundModuleName);
            $output->writeln(sprintf("<info>Directory: </info><comment>%s</comment>\n", $foundModulePath));
            $totalMissing += $this->analyseModule($input, $output, $foundModulePath, $projectPsr4Namespaces);
        }

        if ($input->getOption('check') && $totalMissing > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Find Magento 2 modules in the given paths array
     *
     * @param string[] $paths
     * @return array
     */
    private function getModulesInModulePath(array $paths): array
    {
        $finder = (new Finder())
            ->files()
            ->name('registration.php')
            ->in($paths);

        $scannedModuleDirectories = [];

        foreach ($finder as $registrationFile) {
            $scannedModuleDirectories[] = dirname($registrationFile->getRealPath());
        }

        $installedModulePaths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);

        // verify that path with registration.php is a installed module
        $installedModulePaths = array_filter(
            $installedModulePaths,
            function ($modulePath) use ($scannedModuleDirectories) {
                return in_array(realpath($modulePath), $scannedModuleDirectories);
            }
        );

        return $installedModulePaths;
    }

    /**
     * Analyse single module and detect dependencies for composer.json file
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $modulePath
     * @param array $projectPsr4Namespaces
     * @return int Number of missing dependencies
     */
    private function analyseModule(
        InputInterface $input,
        OutputInterface $output,
        string $modulePath,
        array $projectPsr4Namespaces
    ) : int {
        $moduleFiles = $this->getModuleFiles($modulePath);

        if (isset($moduleFiles['composer.json'])) {
            $moduleComposerJsonContent = json_decode(
                $moduleFiles['composer.json']->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } else {
            throw new \RuntimeException('Found module does not contain composer.json');
        }

        $namespaces = $this->getNamespacesFromFiles($moduleFiles);

        $dependencies = $this->getDependencies(
            $moduleFiles,
            $namespaces,
            $projectPsr4Namespaces,
            $moduleComposerJsonContent
        );

        $origRequire = [];
        if (array_key_exists('require', $moduleComposerJsonContent)) {
            $origRequire = $moduleComposerJsonContent['require'];
        }

        $missingKeys = array_diff(array_keys($dependencies), array_keys($origRequire));
        $difference = [];
        foreach ($dependencies as $packageName => $version) {
            $key = array_search($packageName, $missingKeys, true);
            if ($key !== false) {
                $difference[$packageName] = $version;
            }
        }

        $showOnlyMissing = $input->getOption('only-missing');

        $showOkMessage = false;
        if (!empty($dependencies)) {
            $output->writeln('<info>You should adjust the composer.json require section: </info>');
            if ($showOnlyMissing) {
                $output->writeln('<warning>Recommended composer.json (only missing dependencies shown):</warning>');
                $output->writeln('<fg=cyan>' . $this->formatOutput($difference) . '</>');
            } else {
                $output->writeln('<warning>Recommended composer.json:</warning>');
                $output->writeln('<fg=cyan>' . $this->formatOutput($dependencies) . '</>');
            }
        } else {
            $showOkMessage = true;
        }

        if ($showOkMessage === true) {
            $output->writeln(
                '<info>Dependencies currently defined in module '
                . 'composer.json look good, no change needed</info>'
            );
        }

        return count($difference);
    }

    /**
     * Get relevant module files to check against dependencies in array form
     *
     * e.g. 'etc/module.xml' => Object SplFileInfo
     *
     * @param string $modulePath
     * @return array
     */
    private function getModuleFiles(string $modulePath): array
    {
        $finder = Finder::create()
            ->in($modulePath)
            ->name('composer.json')
            ->name('*.xml')
            ->name('*.php')
            ->name('*.phtml');

        $files = iterator_to_array($finder->getIterator());

        $moduleFiles = [];

        // We need relative paths
        foreach ($files as $file) {
            /** @var $file \Symfony\Component\Finder\SplFileInfo */
            $moduleFiles[$file->getRelativePathname()] = $file;
        }

        return $moduleFiles;
    }

    /**
     * Return unique array of found namespaces in module files
     *
     * @param SplFileInfo[] $files
     * @return array
     */
    private function getNamespacesFromFiles(array $files): array
    {
        $tempMatches = [];

        foreach ($files as $file) {
            $pattern = '/([A-z0-9]+' . preg_quote('\\', '/') . '){2}/';

            preg_match_all($pattern, $file->getContents(), $matches);

            foreach ($matches[0] as $match) {
                $removeLeadingSlash = ltrim($match, '\\');
                $splitNamespace = explode('\\', $removeLeadingSlash, 3);
                if (isset($splitNamespace[1])) {
                    $tempMatches[] = $splitNamespace[0] . '\\' . $splitNamespace[1] . '\\';
                }
            }
        }

        return array_unique($tempMatches);
    }

    /**
     * Get all dependencies for module
     *
     * @param array $moduleFiles
     * @param array $namespaces
     * @param array $projectPsr4Namespaces
     * @param array $moduleComposerJsonContent
     * @return array|string[]
     */
    private function getDependencies(
        array $moduleFiles,
        array $namespaces,
        array $projectPsr4Namespaces,
        array $moduleComposerJsonContent
    ) {
        $dependencies = $this->getSoftDependencies($moduleFiles);

        foreach ($namespaces as $namespace) {
            if (array_key_exists($namespace, $projectPsr4Namespaces) && $namespace !== 'Magento\\Framework\\') {
                $dependencyPackagePath = (string)$projectPsr4Namespaces[$namespace][0];
                $composerFilePath = preg_replace('/\/src$/', '', $dependencyPackagePath);
                $composerFilePath = preg_replace('/\/Psr\/Log$/', '', $composerFilePath);
                $dependencies = \array_merge($dependencies, $this->getComposerJsonVersionConstraint($composerFilePath));
            }
        }

        if (empty($dependencies)) {
            // add at least the framework dependency
            $dependencies['magento/framework'] = $this->getVersionConstraintByPackageName(
                'magento/framework'
            );
        }

        // remove module itself from dependency list
        unset($dependencies[$moduleComposerJsonContent['name']]);

        ksort($dependencies);

        return $dependencies;
    }

    /**
     * Get all soft dependencies for a module
     *
     * @param array $files
     * @return array|string[]
     */
    private function getSoftDependencies(array $files)
    {
        $registeredModulesPaths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);

        $result = [];
        /** @var SplFileInfo $moduleXmlFile */
        $moduleXmlFile = $files['etc/module.xml'];

        $moduleXmlLoaded = simplexml_load_string($moduleXmlFile->getContents());
        if (isset($moduleXmlLoaded->module->sequence)) {
            foreach ($moduleXmlLoaded->module->sequence->module as $module) {
                $moduleName = (string)$module->attributes()->name;
                if (array_key_exists($moduleName, $registeredModulesPaths)) {
                    $composerFilePath = preg_replace('/\/src$/', '', $registeredModulesPaths[$moduleName]);
                    $result = \array_merge($result, $this->getComposerJsonVersionConstraint($composerFilePath));
                }
            }
        }

        return $result;
    }

    /**
     * Returns an array with 1 element key = packageName, value = version constraint
     *
     * @param string $folderName
     * @return array|string[]
     * @throws \JsonException
     */
    private function getComposerJsonVersionConstraint(string $folderName): array
    {
        $composerJson = [];
        /** @var File $fileSystemInfo */
        $fileSystemInfo = new File();

        $compoerFilePath = $folderName . '/composer.json';
        $composerFileContent = $fileSystemInfo->read($compoerFilePath);

        if ($composerFileContent === false) {
            return [self::COMPOSER_FILE_NOT_FOUND => $folderName];
        }

        $content = json_decode($composerFileContent, true, 512, JSON_THROW_ON_ERROR);
        $composerJson[$content['name']] = $this->getVersionConstraintByPackageName($content['name']);

        return $composerJson;
    }

    /**
     * Format composer.json suggested "require" section for output
     *
     * @param array $dependencies
     * @return string
     */
    private function formatOutput(array $dependencies): string
    {
        $composerFileNotFound = '';
        $output = '"require": { ' . "\n";

        foreach ($dependencies as $name => $version) {

            /**
             * remove patch level (e.g. -p5) from version
             * @link https://github.com/netz98/n98-magerun2/issues/1358
             */
            $version = preg_replace('/-p[0-9]+$/', '', $version);

            if ($name === self::COMPOSER_FILE_NOT_FOUND) {
                $composerFileNotFound .= 'file: ' . $version;
            }
            if ($name !== self::COMPOSER_FILE_NOT_FOUND) {
                $output .= "\t" . '"' . $name . '": "' . $version . '",' . "\n";
            }
        }

        $output = rtrim($output, ",\n");

        $output .= "\n}";

        if (!empty($composerFileNotFound)) {
            $output .= "\n\n<warning>Non composer dependency found! " . $composerFileNotFound . "</warning>\n";
        }

        return $output;
    }

    private function loadProjectComposerPackagesByLockFile(string $magentoRootPath)
    {
        $composerLock = new ComposerLock($magentoRootPath);
        $packages = $composerLock->getPackages();

        $this->localInstalledPackagesFromLockFile = [];

        foreach ($packages as $package) {
            $this->localInstalledPackagesFromLockFile[$package->name] = $package->version;
        }
    }

    /**
     * @param string $packageName
     * @return string
     */
    private function getVersionConstraintByPackageName(string $packageName)
    {
        // That's the case if the Magento core is not installed via Composer
        // e.g. the source code version for core developers.
        if (!isset($this->localInstalledPackagesFromLockFile[$packageName])) {
            return '*';
        }

        $installedVersion = $this->localInstalledPackagesFromLockFile[$packageName];

        if ($installedVersion !== '') {
            if (is_numeric($installedVersion[0])) {
                return '^' . $installedVersion;
            }

            if (strlen($installedVersion) > 1
                && $installedVersion[0] === 'v' && is_numeric($installedVersion[1])) {
                return '^' . ltrim($installedVersion, 'v');
            }

            return '*';
        }

        return '*';
    }
}
