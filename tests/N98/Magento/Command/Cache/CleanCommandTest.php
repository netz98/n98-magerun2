<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class CleanCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('cache:clean', 'config cache cleaned');
    }

    public function testItCanCleanMultipleCaches()
    {
        $input = [
            'command' => 'cache:clean',
            'type'    => ['config', 'layout'],

        ];
        $this->assertDisplayContains($input, 'config cache cleaned');
        $this->assertDisplayContains($input, 'layout cache cleaned');
    }

    public function testItSkipsUnknownCacheType()
    {
        $input = [
            'command' => 'cache:clean',
            'type'    => ['block_html,full_page', 'config'],
        ];
        $this->assertDisplayContains($input, '"block_html,full_page" skipped');
        $this->assertDisplayContains($input, 'config cache cleaned');
    }

    public function testItAvoidsUnintentionalCleaningOfAllCaches()
    {
        $input = [
            'command' => 'cache:clean',
            'type'    => ['block_html,full_page'],
        ];
        $this->assertDisplayContains($input, '"block_html,full_page" skipped');
        $this->assertDisplayContains($input, 'Aborting clean');
    }
}
