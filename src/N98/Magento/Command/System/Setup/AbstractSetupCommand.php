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
     * Determine if a module exists. If it does, return the actual module name (not lowercased).
     * @param  string $requestedModuleName
     * @return string
     * @throws \InvalidArgumentException When the module doesn't exist
     */
    public function getModuleName($requestedModuleName)
    {
        $lowercaseModuleName = strtolower($requestedModuleName);
        
        foreach ($this->getModuleList()->getAll() as $moduleName => $moduleInfo) {
            if ($lowercaseModuleName === strtolower($moduleName)) {
                return $moduleName;
            }
        }
        
        throw new \InvalidArgumentException(sprintf('Module does not exist: "%s"', $requestedModuleName));
    }

    /**
     * @return ModuleListInterface
     */
    public function getModuleList()
    {
        if (is_null($this->moduleList)) {
            $this->moduleList = $this->getObjectManager()->get('Magento\Framework\Module\ModuleListInterface');
        }
        return $this->moduleList;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        if (is_null($this->resource)) {
            $this->resource = $this->getObjectManager()->get('Magento\Framework\Module\ResourceInterface');
        }
        return $this->resource;
    }
}
