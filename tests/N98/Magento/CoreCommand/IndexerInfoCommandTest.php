<?php

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class IndexerInfoCommandTest extends AbstractMagentoCoreCommandTestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'indexer:info',
            'catalogsearch_fulltext'
        );
    }
}
