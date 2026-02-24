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

class CsvRendererTest extends TestCase
{
    public function testRender()
    {
        $renderer = new CsvRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'val1', 'col2' => 'val2'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $csvOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedOutput = "col1,col2\nval1,val2\n";
        $this->assertEquals($expectedOutput, str_replace("\r\n", "\n", $csvOutput));
    }

    public function testRenderEmptyRows()
    {
        $renderer = new CsvRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [];

        $renderer->render($output, $rows);

        rewind($stream);
        $csvOutput = stream_get_contents($stream);
        fclose($stream);

        $this->assertEquals('', $csvOutput);
    }

    public function testRenderMultipleRows()
    {
        $renderer = new CsvRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'val1', 'col2' => 'val2'],
            ['col1' => 'val3', 'col2' => 'val4'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $csvOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedOutput = "col1,col2\nval1,val2\nval3,val4\n";
        $this->assertEquals($expectedOutput, str_replace("\r\n", "\n", $csvOutput));
    }
}
