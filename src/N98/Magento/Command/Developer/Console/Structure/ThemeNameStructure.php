<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console\Structure;

/**
 * Class ThemeNameStructure
 * @package N98\Magento\Command\Developer\Console\Structure
 */
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
