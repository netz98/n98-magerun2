<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Structure;

class ThemeNameStructure
{
    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $area
     * @param string $package
     * @param string $name
     */
    public function __construct($area, $package, $name)
    {
        $this->area = $area;
        $this->package = ucfirst($package);
        $this->name = strtolower($name);
    }

    /**
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getArea() . '/' . $this->getPackage() . '/' . $this->getName();
    }
}
