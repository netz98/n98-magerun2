<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\TestCase;

class SymlinksCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command'  => 'dev:symlinks',
                '--global' => true,
                '--on'     => true,
            ],
            'Symlinks allowed'
        );

        $this->assertDisplayContains(
            [
                'command'  => 'dev:symlinks',
                '--global' => true,
                '--off'    => true,
            ],
            'Symlinks denied'
        );
    }
}
