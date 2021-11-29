<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class InfoCurrencyListCommandTest extends AbstractMagentoCoreCommandTestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'info:currency:list',
            'US Dollar'
        );
        $this->assertDisplayContains(
            'info:currency:list',
            'USD'
        );
        $this->assertDisplayContains(
            'info:currency:list',
            'Euro'
        );
        $this->assertDisplayContains(
            'info:currency:list',
            'EUR'
        );
    }
}
