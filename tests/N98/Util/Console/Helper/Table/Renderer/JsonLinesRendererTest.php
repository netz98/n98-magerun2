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

class JsonLinesRendererTest extends TestCase
{
    public function testRender(): void
    {
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        (new JsonLinesRenderer())->render($output, [
            ['name' => 'Alice', 'active' => true],
            ['name' => 'Bob', 'active' => false],
        ]);

        rewind($stream);
        self::assertSame(
            "{\"name\":\"Alice\",\"active\":true}\n{\"name\":\"Bob\",\"active\":false}\n",
            stream_get_contents($stream)
        );
        fclose($stream);
    }

    public function testRenderEmptyRows(): void
    {
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        (new JsonLinesRenderer())->render($output, []);

        rewind($stream);
        self::assertSame('', stream_get_contents($stream));
        fclose($stream);
    }
}
