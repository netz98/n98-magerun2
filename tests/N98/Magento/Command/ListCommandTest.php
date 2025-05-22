<?php

namespace N98\Magento\Command;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'list',
            sprintf('n98-magerun2 %s (commit: @git_commit_short@) by valantic CEC', $this->getApplication()->getVersion())
        );
    }
}
