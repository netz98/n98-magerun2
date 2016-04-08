<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Structure;

class ModuleNameStructure
{
    /**
     * @var string
     */
    private $vendorName;

    /**
     * @var string
     */
    private $shortModuleName;

    /**
     * @param string $fullModuleName like Acme_Foo
     */
    public function __construct($fullModuleName)
    {
        $parts = explode('_', $fullModuleName);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Please specify a correct module name like Acme_Foo');
        }

        $this->vendorName = ucfirst($parts[0]);
        $this->shortModuleName = ucfirst($parts[1]);
    }

    /**
     * @return string
     */
    public function getShortModuleName()
    {
        return $this->shortModuleName;
    }

    /**
     * Returns the full module name in style Acme_Foo
     *
     * @return string
     */
    public function getFullModuleName()
    {
        return $this->vendorName . '_' . $this->shortModuleName;
    }

    /**
     * @return string
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullModuleName();
    }
}