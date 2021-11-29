<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class CacheStatusCommandTest extends AbstractMagentoCoreCommandTest
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'cache:status',
            'Current status:'
        );
    }
}
