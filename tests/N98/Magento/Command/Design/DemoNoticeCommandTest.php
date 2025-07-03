<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Design;

use N98\Magento\Command\TestCase;

class DemoNoticeCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command'  => 'design:demo-notice',
                '--global' => true,
                '--on'     => true,
            ],
            'Demo Notice enabled'
        );

        $this->assertDisplayContains(
            [
                'command'  => 'design:demo-notice',
                '--global' => true,
                '--off'    => true,
            ],
            'Demo Notice disabled'
        );
    }
}
