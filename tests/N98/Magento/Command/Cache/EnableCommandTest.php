<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class EnableCommandTest extends TestCase
{
    const NONEXISTENT_CACHE_TYPE = 'FAKE_CACHE_TYPE';

    public function testEnableNonexistentCache()
    {
        $input = [
            'command' => 'cache:enable',
            'type'    => self::NONEXISTENT_CACHE_TYPE,
        ];
        $expectedOutput = $this->getExpectedOutput();

        $this->assertDisplayContains($input, $expectedOutput);
    }

    /**
     * @return string
     */
    private function getExpectedOutput()
    {
        $buffer =
            sprintf(
                EnableCommand::INVALID_TYPES_MESSAGE,
                self::NONEXISTENT_CACHE_TYPE
            ) . PHP_EOL . EnableCommand::ABORT_MESSAGE . PHP_EOL;

        // Strip tags because of console formatting (<info> etc)
        return strip_tags($buffer);
    }
}
