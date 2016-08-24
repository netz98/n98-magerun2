<?php
/*
 * @author Tom Klingenberg <t.klingenberg@netz98.de>
 * @copyright Copyright (c) 2016 netz98 new media GmbH (http://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

namespace N98\Magento\Api;


use BadMethodCallException;
use Magento\Framework\Module\ResourceInterface as ModuleResourceInterface;

class ModuleVersion implements ModuleInterface
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var ModuleResource
     */
    private $resource;

    public function __construct(Module $module, ModuleResourceInterface $resource)
    {
        $this->module = $module;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->module->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->module->getVersion();
    }

    /**
     * @return string
     */
    public function getDataVersion()
    {
        $name = $this->getName();
        $version = $this->resource->getDataVersion($name);
        if ($version === false) {
            throw new BadMethodCallException(sprintf("Module '%s' data-version is not available.", $name));
        }
        return $version;
    }

    /**
     * @return string
     */
    public function getDbVersion()
    {
        $name = $this->getName();
        $version = $this->resource->getDbVersion($name);
        if ($version === false) {
            throw new BadMethodCallException(sprintf("Module '%s' db-version is not available.", $name));
        }
        return $version;
    }

    /**
     * @param string $version
     */
    public function setDataVersion($version)
    {
        $this->resource->setDataVersion($this->getName(), $version);
    }

    /**
     * @param string $version
     */
    public function setDbVersion($version)
    {
        $this->resource->setDbVersion($this->getName(), $version);
    }
}
