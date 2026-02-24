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

class JsonRendererTest extends TestCase
{
    public function testRender()
    {
        $renderer = new JsonRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'val1', 'col2' => 'val2'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $jsonOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedData = [
            0 => ['col1' => 'val1', 'col2' => 'val2'],
        ];
        $expectedOutput = json_encode($expectedData, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT) . "\n";

        $this->assertEquals($expectedOutput, str_replace("\r\n", "\n", $jsonOutput));
    }

    public function testRenderEmpty()
    {
        $renderer = new JsonRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [];

        $renderer->render($output, $rows);

        rewind($stream);
        $jsonOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedOutput = "{}\n";

        $this->assertEquals($expectedOutput, str_replace("\r\n", "\n", $jsonOutput));
    }
}
