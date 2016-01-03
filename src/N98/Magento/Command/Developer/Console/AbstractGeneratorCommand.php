<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Filesystem\Directory\WriteFactory as DirectoryWriteFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory as DirectoryReadFactory;
use Zend\Filter\Word\SeparatorToSeparator;

abstract class AbstractGeneratorCommand extends AbstractConsoleCommand
{
    /**
     * @param string $type
     * @return string
     */
    protected function getCurrentModulePath($type = '')
    {
        return $this->get(ModuleDir::class)->getDir($this->getCurrentModuleName(), $type);
    }

    /**
     * @return string
     */
    protected function getCurrentModuleNamespace()
    {
        $moduleName = $this->getCurrentModuleName();
        list($vendorPrefix, $moduleNamespace) = explode('_', $moduleName);

        return $vendorPrefix . '\\' . $moduleNamespace;
    }

    /**
     * @return string
     */
    protected function getCurrentModuleName()
    {
        try {
            $currentModuleName = $this->getScopeVariable('_current_module');
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Module not defined. Please use "module <name>" command');
        }

        return $currentModuleName;
    }

    /**
     * @param string $name
     */
    protected function setCurrentModuleName($name)
    {
        $this->setScopeVariable('_current_module', $name);
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\Write
     */
    protected function getCurrentModuleDirectoryWriter()
    {
        $directoryWrite = $this->get(DirectoryWriteFactory::class);
        /** @var $directoryWrite DirectoryWriteFactory */
        return $directoryWrite->create($this->getCurrentModulePath());
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\Read
     */
    protected function getCurrentModuleDirectoryReader()
    {
        $directoryRead = $this->get(DirectoryWriteFactory::class);
        /** @var $directoryRead DirectoryReadFactory */
        return $directoryRead->create($this->getCurrentModulePath());
    }

    /**
     * @param string $pathArgument
     * @return string
     */
    protected function getNormalizedPathByArgument($pathArgument)
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
    protected function getNormalizedClassnameByArgument($pathArgument)
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

}