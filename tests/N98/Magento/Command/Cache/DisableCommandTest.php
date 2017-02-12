<?php
/**
 * @todo    writing unit tests for toggling caches is complicated because environments differ
 *          touching caches changes state and will result in different test result second time
 *          solutions: disabling/enabling, faking&mocking or having one defined test environment
 */

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class DisableCommandTest extends TestCase
{
    const NONEXISTENT_CACHE_TYPE = 'FAKE_CACHE_TYPE';

    public function testDisableNonexistentCache()
    {
        $expectedOutput = $this->getExpectedOutput();

        $input = array(
            'command' => 'cache:disable',
            'type'    => self::NONEXISTENT_CACHE_TYPE,
        );

        $this->assertDisplayContains($input, $expectedOutput);
    }

    /**
     * @return string
     */
    private function getExpectedOutput()
    {
        $buffer =
            sprintf(
                DisableCommand::INVALID_TYPES_MESSAGE,
                self::NONEXISTENT_CACHE_TYPE
            ) . PHP_EOL . DisableCommand::ABORT_MESSAGE . PHP_EOL;

        // Strip tags because of console formatting (<info> etc)
        return strip_tags($buffer);
    }
}
