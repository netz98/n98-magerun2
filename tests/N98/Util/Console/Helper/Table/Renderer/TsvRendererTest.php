<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\StreamOutput;

class TsvRendererTest extends TestCase
{
    public function testRender(): void
    {
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        (new TsvRenderer())->render($output, [
            ['col1' => 'val1', 'col2' => 'val2'],
            ['col1' => 'val3', 'col2' => 'val4'],
        ]);

        rewind($stream);
        self::assertSame("col1\tcol2\nval1\tval2\nval3\tval4\n", stream_get_contents($stream));
        fclose($stream);
    }
}
