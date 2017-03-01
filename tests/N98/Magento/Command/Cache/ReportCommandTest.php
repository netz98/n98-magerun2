<?php

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class ReportCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayRegExp(
            array(
                'command' => 'cache:report',
            ),
            '~\\| ID.*\\| EXPIRE.*\\|$~m'
        );
    }
}
