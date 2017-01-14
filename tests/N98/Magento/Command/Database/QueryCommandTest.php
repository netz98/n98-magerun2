<?php

namespace N98\Magento\Command\Database;

use N98\Magento\Command\TestCase;

class QueryCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = array(
            'command' => 'db:query',
            'query'   => 'SHOW TABLES;',
        );
        $this->assertDisplayContains($input, 'admin_user');
        $this->assertDisplayContains($input, 'catalog_product_entity');
        $this->assertDisplayContains($input, 'wishlist');
    }
}
