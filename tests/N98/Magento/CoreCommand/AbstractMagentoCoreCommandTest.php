<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

use N98\Magento\Command\TestCase;

abstract class AbstractMagentoCoreCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->registerCoreCommands();
    }
}
