<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Store;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:store:list', 'id');
        $this->assertDisplayContains('sys:store:list', 'code');
    }

    public function testExecuteJsonFormat()
    {
        $tester = $this->assertExecute([
            'command' => 'sys:store:list',
            '--format' => 'json',
        ]);

        $display = $tester->getDisplay();
        $this->assertStringStartsWith('{', ltrim($display));

        $data = json_decode($display, true);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $first = reset($data);
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('website_id', $first);
        $this->assertArrayHasKey('group_id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('code', $first);
        $this->assertArrayHasKey('sort_order', $first);
        $this->assertArrayHasKey('is_active', $first);
    }
}
