<?php

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class DevDiInfoCommandTest extends AbstractMagentoCoreCommandTestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command' => 'dev:di:info',
                'class' => 'Magento\\Catalog\\Api\\Data\\ProductInterface',
            ],
            'DI configuration for the class Magento\Catalog\Api\Data\ProductInterface in the GLOBAL area'
        );
    }
}
