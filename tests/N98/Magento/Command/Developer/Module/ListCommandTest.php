<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    const NONEXISTENT_VENDOR = 'FAKE_VENDOR';
    const MODULE_OCCURENCE_CHECK = 'Magento_Catalog';

    /**
     * Test whether the $moduleList property is filled
     */
    public function testBasicList()
    {
        /* @var $command ListCommand */
        $command = $this->assertExecute('dev:module:list')->getCommand();
        $this->assertNotEmpty($command->getModuleList());
    }

    /**
     * Sanity test to check whether Magento_Core occurs in the output
     */
    public function testMagentoCatalogOccurs()
    {
        $this->assertDisplayContains('dev:module:list', self::MODULE_OCCURENCE_CHECK);
    }

    /**
     * Test whether we can filter on vendor (by checking a non-existent vendor, we should get an empty list)
     */
    public function testVendorList()
    {
        /* @var $command ListCommand */
        $command = $this->assertExecute(
            ['command' => 'dev:module:list', '--vendor' => self::NONEXISTENT_VENDOR]
        )->getCommand();
        $this->assertEmpty($command->getModuleList());
    }
}
