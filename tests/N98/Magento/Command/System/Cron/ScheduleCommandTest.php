<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\TestCase;

class ScheduleCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = [
            'command' => 'sys:cron:schedule',
            'job'     => 'backend_clean_cache',
        ];
        $this->assertDisplayContains($input, 'Scheduling Magento\Backend\Cron\CleanCache::execute done');
    }
}
