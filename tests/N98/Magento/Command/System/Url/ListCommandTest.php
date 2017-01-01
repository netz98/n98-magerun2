<?php

namespace N98\Magento\Command\System\Url;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = array(
            'linetemplate'     => 'prefix {url} suffix',
            'command'          => 'sys:url:list',
            'stores'           => 0, // admin store
            '--add-categories' => true,
            '--add-products'   => true,
            '--add-cmspages'   => true,
        );

        $this->assertDisplayContains($input, 'prefix');
        $this->assertDisplayContains($input, 'http');
        $this->assertDisplayContains($input, 'suffix');
    }
}
