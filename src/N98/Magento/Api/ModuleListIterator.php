<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <t.klingenberg@netz98.de>
 *
 * @see PROJECT_LICENSE.txt
 */

namespace N98\Magento\Api;

use ArrayIterator;
use IteratorIterator;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class ModuleListIterator
 *
 * @package N98\Magento\Api
 */
class ModuleListIterator extends IteratorIterator
{
    public function __construct(ModuleListInterface $moduleList)
    {
        parent::__construct(new ArrayIterator($moduleList->getAll()));
    }

    /**
     * @return Module
     */
    public function current(): Module
    {
        $current = parent::current();

        return new Module($current['name'], $current['setup_version']);
    }
}
