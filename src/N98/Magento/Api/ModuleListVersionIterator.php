<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <t.klingenberg@netz98.de>
 */

namespace N98\Magento\Api;

use ArrayIterator;
use IteratorIterator;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\ResourceInterface as ModuleResourceInterface;

/**
 * Class ModuleListVersionIterator
 *
 * @package N98\Magento\Api
 */
class ModuleListVersionIterator extends IteratorIterator
{
    /**
     * @var ModuleResourceInterface
     */
    private $resource;

    public function __construct(ModuleListInterface $moduleList, ModuleResourceInterface $resource)
    {
        parent::__construct(new ArrayIterator($moduleList->getAll()));

        $this->resource = $resource;
    }

    /**
     * @return ModuleVersion
     */
    public function current(): ModuleVersion
    {
        $current = parent::current();

        $module = new Module($current['name'], $current['setup_version']);

        return new ModuleVersion($module, $this->resource);
    }
}
