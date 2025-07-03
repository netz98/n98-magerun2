<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console\Structure;

/**
 * Class ModuleNameStructure
 * @package N98\Magento\Command\Developer\Console\Structure
 */
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
