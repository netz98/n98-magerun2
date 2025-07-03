<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class ViewCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command' => 'cache:view',
                'id'      => 'NON_EXISTING_ID',
                '--fpc'   => true,
            ],
            "Cache id NON_EXISTING_ID does not exist (anymore)"
        );
    }
}
