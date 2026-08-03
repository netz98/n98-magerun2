<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class TableHelperTest extends TestCase
{
    public function testInvalidFormatRaisesAnError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown output format "invalid"');

        (new TableHelper())->renderByFormat(new BufferedOutput(), [], 'invalid');
    }

    public function testMissingFormatStillRendersTheDefaultTable(): void
    {
        $output = new BufferedOutput();
        $helper = new TableHelper();
        $helper->setHeaders(['name']);

        $helper->renderByFormat($output, [['Alice']]);

        self::assertStringContainsString('Alice', $output->fetch());
    }
}
