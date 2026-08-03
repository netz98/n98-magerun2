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

class MarkdownRendererTest extends TestCase
{
    public function testRender(): void
    {
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        (new MarkdownRenderer())->render($output, [
            ['name' => 'Alice', 'note' => 'A | B'],
            ['name' => 'Bob', 'note' => "line 1\nline 2"],
        ]);

        rewind($stream);
        self::assertSame(
            "| name | note |\n| --- | --- |\n| Alice | A \\| B |\n| Bob | line 1<br>line 2 |\n",
            stream_get_contents($stream)
        );
        fclose($stream);
    }

    public function testRenderEmptyRows(): void
    {
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        (new MarkdownRenderer())->render($output, []);

        rewind($stream);
        self::assertSame('', stream_get_contents($stream));
        fclose($stream);
    }
}
