<?php
/*
 * @author Tom Klingenberg <t.klingenberg@netz98.de>
 * @copyright Copyright (c) 2016 netz98 new media GmbH (http://www.netz98.de)
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
