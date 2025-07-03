<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Media;

use N98\Magento\Command\TestCase;

class DumpCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'media:dump',
            sprintf('media/theme/preview', $this->getApplication()->getVersion())
        );
    }
}
