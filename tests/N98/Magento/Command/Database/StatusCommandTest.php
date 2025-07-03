<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Magento\Command\TestCase;

class StatusCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = [
            'command'  => 'db:status',
            '--format' => 'csv',
        ];
        $this->assertDisplayContains($input, 'Threads_connected');
        $this->assertDisplayContains($input, 'Innodb_buffer_pool_wait_free');
        $this->assertDisplayContains($input, 'InnoDB Buffer Pool hit');
        $this->assertDisplayContains($input, 'Full table scans');
    }

    public function testSearch()
    {
        $input = [
            'command'  => 'db:status',
            '--format' => 'csv',
            'search'   => 'Innodb%',
        ];
        $this->assertDisplayContains($input, 'Innodb_buffer_pool_read_ahead_rnd');
        $this->assertDisplayContains($input, 'Innodb_buffer_pool_wait_free');
        $this->assertDisplayContains($input, 'InnoDB Buffer Pool hit');
        $this->assertDisplayContains($input, 'Innodb_dblwr_pages_written');
        $this->assertDisplayContains($input, 'Innodb_os_log_written');
    }

    public function testRounding()
    {
        $this->assertDisplayRegExp(
            [
            'command'    => 'db:status',
            '--format'   => 'csv',
            '--rounding' => '2',
            'search'     => '%size%',
            ],
            '~Innodb_page_size,[0-9\.]+K,~'
        );
    }
}
