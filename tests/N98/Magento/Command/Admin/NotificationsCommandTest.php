<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Admin;

use N98\Magento\Command\TestCase;

class NotificationsCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command' => 'admin:notifications',
                '--on'    => true,
            ],
            'Admin Notifications hidden'
        );

        $this->assertDisplayContains(
            [
                'command' => 'admin:notifications',
                '--off'   => true,
            ],
            'Admin Notifications visible'
        );
    }
}
