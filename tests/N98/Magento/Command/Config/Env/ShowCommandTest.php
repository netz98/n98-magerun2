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
 * Class ShowCommandTest
 * @package N98\Magento\Command\Config\Env
 */
class ShowCommandTest extends TestCase
{
    public function testExecute()
    {
        /**
         * Install date should be found
         */
        $this->assertDisplayContains(
            [
                'command' => 'config:env:show',
            ],
            'install.date'
        );
    }

    public function testExecuteWithEmptyArrayValue()
    {
        $this->assertExecute(
            [
                'command' => 'config:env:set',
                '--input-format' => 'json',
                'key' => 'magerun.test',
                'value' => '[]'
            ]
        );

        $this->assertExecute(
            [
                'command' => 'config:env:show',
            ]
        );
    }
}
