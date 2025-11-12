<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'sys:cron:list',
            'Cronjob List'
        );
    }

    public function testExecuteWithFilter()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:cron:list', 'job_name' => 'catalog_product_outdated_price_values_cleanup'],
            'catalog_product_outdated_price_values_cleanup'
        );
    }

    public function testExecuteWithWildcardFilter()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:cron:list', 'job_name' => 'catalog_*'],
            'catalog_product_outdated_price_values_cleanup'
        );
    }

    public function testExecuteWithNonExistentFilter()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:cron:list', 'job_name' => 'non_existent_filter'],
            'No cron jobs found.'
        );
    }
}
