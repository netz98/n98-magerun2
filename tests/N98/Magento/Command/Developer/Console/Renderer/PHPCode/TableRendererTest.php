<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Renderer\PHPCode;

use N98\Magento\Command\Developer\Console\Structure\DDLTable;
use N98\Magento\Command\Developer\Console\TestCase;
use N98\Util\Console\Helper\TwigHelper;

class TableRendererTest extends TestCase
{
    /**
     * @var TableRenderer
     */
    private $renderer;

    /**
     * @var DDLTable
     */
    private $table;
    
    protected function setUp()
    {
        $this->table = new DDLTable();
        $twigHelper = $this->getApplication()->getHelperSet()->get('twig');
        $this->renderer = new TableRenderer($this->table, $twigHelper);
    }
    
    /**
     * @test
     */
    public function itShouldRenderTheTableNameAndComment()
    {
        $this->table->setName('mytable');
        $this->table->setComment('my comment');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/_files/itShouldRenderTheTableNameAndComment.txt'),
            $this->renderer->render()
        );
    }
}

