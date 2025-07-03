<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database\Maintain;

use N98\Magento\Command\TestCase;

/**
 * @see \N98\Magento\Command\Database\Maintain\CheckTablesCommand
 */
class CheckTablesCommandTest extends TestCase
{
    public function testExecuteMyIsam()
    {
        $this->markTestSkipped('Currently we have no myisam tables in a magento2 installation');

        $this->assertDisplayContains(
            [
                'command'  => 'db:maintain:check-tables',
                '--format' => 'csv',
                '--type'   => 'quick',
                '--table'  => 'oauth_nonce',
            ],
            'oauth_nonce,check,quick,OK'
        );
    }

    public function testExecuteInnoDb()
    {
        $input = [
            'command'  => 'db:maintain:check-tables',
            '--format' => 'csv',
            '--type'   => 'quick',
            '--table'  => 'catalog_product_entity_media_gallery*',
        ];
        $timeRegex = '"\s+[0-9]+\srows","[0-9\.]+\ssecs"';

        $this->assertDisplayRegExp(
            $input,
            '~catalog_product_entity_media_gallery,"ENGINE InnoDB",' . $timeRegex . '~'
        );
        $this->assertDisplayRegExp(
            $input,
            '~catalog_product_entity_media_gallery_value,"ENGINE InnoDB",' . $timeRegex . '~'
        );
    }
}
