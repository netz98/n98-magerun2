<?php

namespace N98\Magento\Command\Developer\Console;

use Exception;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Filter\Word\SeparatorToSeparator;
use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Filesystem\Directory\ReadFactory as DirectoryReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory as DirectoryWriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\Dir as ModuleDir;
use N98\Magento\Command\Developer\Console\Structure\ModuleNameStructure;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractGeneratorCommand
 * @package N98\Magento\Command\Developer\Console
 */
abstract class AbstractGeneratorCommand extends AbstractConsoleCommand
{
    /**
     * @var WriteInterface
     */
    protected static $currentModuleDirWriter = null;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->catchedExecute($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function catchedExecute(InputInterface $input, OutputInterface $output)
    {
        /* intentionally left blank, implement or implement execute() */
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCurrentModulePath($type = '')
    {
        $fullModuleName = $this->getCurrentModuleName()->getFullModuleName();

        return $this->get(ModuleDir::class)
            ->getDir($fullModuleName, $type);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getCurrentModuleFilePath($path)
    {
        return $this->getCurrentModulePath() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     */
    public function getCurrentModuleNamespace()
    {
        $moduleName = $this->getCurrentModuleName()->getFullModuleName();

        return $this->getModuleNamespace($moduleName);
    }

    /**
     * @param string $moduleName
     *
     * @return string
     */
    public function getModuleNamespace($moduleName)
    {
        list($vendorPrefix, $moduleNamespace) = explode('_', $moduleName);

        return $vendorPrefix . '\\' . $moduleNamespace;
    }

    /**
     * @return ModuleNameStructure
     */
    public function getCurrentModuleName()
    {
        try {
            $magerunInternal = $this->getScopeVariable('magerunInternal');

            $currentModuleName = $magerunInternal->currentModule;

            if (empty($currentModuleName)) {
                throw new \InvalidArgumentException('No module defined');
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Module not defined. Please use "module <name>" command');
        }

        return new ModuleNameStructure($currentModuleName);
    }

    /**
     * Returns an id generated by current module name
     *
     * @return string
     */
    public function getCurrentModuleId()
    {
        return lcfirst(str_replace('_', '', $this->getCurrentModuleName()));
    }

    /**
     * @param string $name
     */
    public function setCurrentModuleName($name)
    {
        try {
            $magerunInternal = $this->getScopeVariable('magerunInternal');
        } catch (\InvalidArgumentException $e) {
            $magerunInternal = new \stdClass();
        }
        $magerunInternal->currentModule = $name;
        $this->setScopeVariable('magerunInternal', $magerunInternal);

        $this->reset();
    }

    /**
     * @param WriteInterface $currentModuleDirWriter
     */
    public function setCurrentModuleDirectoryWriter(WriteInterface $currentModuleDirWriter)
    {
        self::$currentModuleDirWriter = $currentModuleDirWriter;
    }

    /**
     * @return WriteInterface
     */
    public function getCurrentModuleDirectoryWriter()
    {
        if (self::$currentModuleDirWriter === null) {
            $directoryWrite = $this->create(DirectoryWriteFactory::class);
            /** @var $directoryWrite DirectoryWriteFactory */

            self::$currentModuleDirWriter = $directoryWrite->create($this->getCurrentModulePath());
        }

        return self::$currentModuleDirWriter;
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\Read
     */
    public function getCurrentModuleDirectoryReader()
    {
        $directoryRead = $this->get(DirectoryWriteFactory::class);

        /** @var $directoryRead DirectoryReadFactory */
        return $directoryRead->create($this->getCurrentModulePath());
    }

    /**
     * @param string $pathArgument
     * @return string
     */
    public function getNormalizedPathByArgument($pathArgument)
    {
        $namespaceFilterDot = $this->create(
            SeparatorToSeparator::class,
            ['searchSeparator' => '.', 'replacementSeparator' => DIRECTORY_SEPARATOR]
        );
        $namespaceFilterBackslash = $this->create(
            SeparatorToSeparator::class,
            ['searchSeparator' => '\\', 'replacementSeparator' => DIRECTORY_SEPARATOR]
        );
        $pathArgument = $namespaceFilterDot->filter($pathArgument);
        $pathArgument = $namespaceFilterBackslash->filter($pathArgument);

        $parts = explode(DIRECTORY_SEPARATOR, $pathArgument);

        return implode(DIRECTORY_SEPARATOR, array_map('ucfirst', $parts));
    }

    /**
     * @param string $pathArgument
     * @return string
     */
    public function getNormalizedClassnameByArgument($pathArgument)
    {
        $namespaceFilterDot = $this->create(
            SeparatorToSeparator::class,
            ['searchSeparator' => '.', 'replacementSeparator' => '\\']
        );
        $namespaceFilterBackslash = $this->create(
            SeparatorToSeparator::class,
            ['searchSeparator' => '.', 'replacementSeparator' => '\\']
        );

        $pathArgument = $namespaceFilterDot->filter($pathArgument);
        $pathArgument = $namespaceFilterBackslash->filter($pathArgument);

        $parts = explode('\\', $pathArgument);

        return implode('\\', array_map('ucfirst', $parts));
    }

    /**
     * @param OutputInterface $output
     * @param ClassGenerator $classGenerator
     * @param string $filePathToGenerate
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function writeClassToFile(
        OutputInterface $output,
        ClassGenerator $classGenerator,
        $filePathToGenerate
    ) {
        $fileGenerator = FileGenerator::fromArray(
            [
                'classes' => [$classGenerator],
            ]
        );

        $this->getCurrentModuleDirectoryWriter()
            ->writeFile($filePathToGenerate, $fileGenerator->generate());

        $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
    }

    /**
     * Reset internal caches etc.
     */
    protected function reset()
    {
        self::$currentModuleDirWriter = null;
    }
}
