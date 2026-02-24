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

class JsonArrayRendererTest extends TestCase
{
    /**
     * @covers \N98\Util\Console\Helper\Table\Renderer\JsonArrayRenderer::render
     */
    public function testRender()
    {
        $renderer = new JsonArrayRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'val1', 'col2' => 'val2'],
            ['col1' => 'val3', 'col2' => 'val4'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $buffer = stream_get_contents($stream);
        fclose($stream);

        $expectedJson = json_encode($rows, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $this->assertEquals($expectedJson . PHP_EOL, $buffer);
    }

    /**
     * @covers \N98\Util\Console\Helper\Table\Renderer\JsonArrayRenderer::render
     */
    public function testRenderAssociativeRows()
    {
        $renderer = new JsonArrayRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            'row1' => ['col1' => 'val1'],
            'row2' => ['col1' => 'val2'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $buffer = stream_get_contents($stream);
        fclose($stream);

        $expectedRows = array_values($rows);
        $expectedJson = json_encode($expectedRows, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $this->assertEquals($expectedJson . PHP_EOL, $buffer);
    }

    /**
     * @covers \N98\Util\Console\Helper\Table\Renderer\JsonArrayRenderer::render
     */
    public function testRenderEmpty()
    {
        $renderer = new JsonArrayRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [];

        $renderer->render($output, $rows);

        rewind($stream);
        $buffer = stream_get_contents($stream);
        fclose($stream);

        $expectedJson = json_encode([], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $this->assertEquals($expectedJson . PHP_EOL, $buffer);
    }
}
