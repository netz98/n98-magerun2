<?php

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
