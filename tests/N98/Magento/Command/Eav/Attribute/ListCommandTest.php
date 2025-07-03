<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayRegExp(
            [
                'command'      => 'eav:attribute:list',
                '--add-source' => true,
            ],
            '~\\| code.*\\| id.*\\| entity_type.*\\| label.*\\| source.*\\|$~m'
        );

        $this->assertDisplayContains(
            [
                'command'       => 'eav:attribute:list',
                '--filter-type' => 'catalog_product',
            ],
            'sku'
        );
    }
}
