<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Renderer\PHPCode;

use Magento\Framework\DB\Ddl\Table;
use N98\Magento\Command\Developer\Console\Structure\DDLTable;
use N98\Magento\Command\Developer\Console\Structure\DDLTableColumn;
use N98\Magento\Command\Developer\Console\TestCase;

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
        $this->createBasicTable();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/_files/itShouldRenderTheTableNameAndComment.txt'),
            $this->renderer->render()
        );
    }

    /**
     * @test
     */
    public function itShouldRenderAnIntegerColumn()
    {
        $this->createBasicTable();

        $column = new DDLTableColumn();
        $column->setType(Table::TYPE_INTEGER);
        $column->setName('mycolumn');
        $column->setComment('column comment');
        $column->setUnsigned(true);
        $column->setNullable(false);
        $column->setDefault(200);
        $column->setPrimary(false);
        $column->setIdentity(false);

        $this->table->setColumns([$column]);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/_files/itShouldRenderAnIntegerColumn.txt'),
            $this->renderer->render()
        );
    }

    /**
     * @test
     */
    public function itShouldRenderATextColumn()
    {
        $this->createBasicTable();

        $column = new DDLTableColumn();
        $column->setSize(1024);
        $column->setType(Table::TYPE_TEXT);
        $column->setName('mycolumn');
        $column->setComment('column comment');
        $column->setDefault('my default');

        $this->table->setColumns([$column]);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/_files/itShouldRenderATextColumn.txt'),
            $this->renderer->render()
        );
    }

    private function createBasicTable()
    {
        $this->table->setName('mytable');
        $this->table->setComment('my comment');
    }
}


