<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Magento\Command\TestCase;

class VariablesCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = [
            'command'  => 'db:variables',
            '--format' => 'csv',
        ];

        $this->assertDisplayContains($input, 'innodb_log_buffer_size');
    }

    public function testSearch()
    {
        $input = [
            'command'  => 'db:variables',
            '--format' => 'csv',
            'search'   => 'Innodb%',
        ];

        $this->assertDisplayContains($input, 'innodb_log_file_size');
        $this->assertDisplayContains($input, 'innodb_read_io_threads');
    }

    public function testRounding()
    {
        $input = [
            'command'    => 'db:variables',
            '--format'   => 'csv',
            '--rounding' => '2',
            'search'     => '%size%',
        ];

        $this->assertDisplayRegExp($input, '~max_binlog_stmt_cache_size,[0-9\.]+[A-Z]~');
        $this->assertDisplayRegExp($input, '~myisam_max_sort_file_size,[0-9\.]+[A-Z]~');
    }
}
