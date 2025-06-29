<?php

namespace N98\Magento\Command\Developer\Di\Plugin;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command' => 'dev:di:plugin:list',
                'class'   => 'Magento\\Catalog\\Api\\ProductRepositoryInterface',
            ],
            'remove_images_from_gallery_after_removing_product'
        );
    }
}
