<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Renderer\PHPCode;

use N98\Magento\Command\Developer\Console\Structure\DDLTable;
use N98\Util\Console\Helper\TwigHelper;

class TableRenderer implements PHPCodeRendererInterface
{
    /**
     * @var DDLTable
     */
    private $table;
    /**
     * @var TwigHelper
     */
    private $twigHelper;

    /**
     * @param DDLTable $table
     * @param TwigHelper $twigHelper
     */
    public function __construct(DDLTable $table, TwigHelper $twigHelper)
    {
        $this->table = $table;
        $this->twigHelper = $twigHelper;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render()
    {
        return $this->twigHelper->render('dev/console/make/table.twig', ['table' => $this->table]);
    }
}
