<?php

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\AbstractMagentoCommand;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\ResourceInterface;

/**
 * Class AbstractSetupCommand
 * @package N98\Magento\Command\System\Setup
 */
abstract class AbstractSetupCommand extends AbstractMagentoCommand
{
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * Gather dependencies
     * @param ModuleListInterface $moduleList
     * @param ResourceInterface   $resource
     */
    public function inject(
        ModuleListInterface $moduleList,
        ResourceInterface $resource
    ) {
        $this->moduleList = $moduleList;
        $this->resource = $resource;
    }

    /**
     * Determine if a module exists. If it does, return the actual module name (not lowercased).
     * @param  string $requestedModuleName
     * @return string
     * @throws \InvalidArgumentException When the module doesn't exist
     */
    public function getMagentoModuleName($requestedModuleName)
    {
        $lowercaseModuleName = strtolower($requestedModuleName);
        foreach ($this->getMagentoModuleList() as $moduleName => $moduleInfo) {
            if ($lowercaseModuleName === strtolower($moduleName)) {
                return $moduleName;
            }
        }
        
        throw new \InvalidArgumentException(sprintf('Module does not exist: "%s"', $requestedModuleName));
    }

    /**
     * @return array
     */
    protected function getMagentoModuleList()
    {
        return $this->moduleList->getAll();
    }

    /**
     * @return ResourceInterface
     */
    protected function getMagentoModuleResource()
    {
        return $this->resource;
    }
}
