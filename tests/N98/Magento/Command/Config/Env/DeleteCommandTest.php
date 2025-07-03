<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Env;

use N98\Magento\Command\TestCase;

/**
 * Class DeleteCommandTest
 * @package N98\Magento\Command\Config\Env
 */
class DeleteCommandTest extends TestCase
{
    public function testExecute()
    {
        // first add a dummy key
        $this->assertExecute(
            [
                'command' => 'config:env:set',
                'key' => 'magerun.test',
                'value' => 'test'
            ]
        );

        // Check if config gets removed
        $this->assertDisplayContains(
            [
                'command' => 'config:env:delete',
                'key' => 'magerun.test'
            ],
            'Config magerun.test successfully removed'
        );

        // Check for idempotency
        $this->assertDisplayContains(
            [
                'command' => 'config:env:delete',
                'key' => 'magerun.test',
                '--verbose' => true // Add dummy option to force different input hash
            ],
            'Config doesn\'t exists'
        );
    }
}
