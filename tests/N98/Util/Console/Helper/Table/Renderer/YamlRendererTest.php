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
use Symfony\Component\Yaml\Yaml;

class YamlRendererTest extends TestCase
{
    public function testRender()
    {
        $renderer = new YamlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'val1', 'col2' => 'val2'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $yamlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedOutput = Yaml::dump($rows) . "\n";
        $this->assertEquals($expectedOutput, $yamlOutput);
    }

    public function testRenderEmptyRows()
    {
        $renderer = new YamlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [];

        $renderer->render($output, $rows);

        rewind($stream);
        $yamlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedOutput = Yaml::dump($rows) . "\n";
        $this->assertEquals($expectedOutput, $yamlOutput);
    }

    public function testRenderNestedArray()
    {
        $renderer = new YamlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            'row1' => ['col1' => 'val1', 'col2' => 'val2'],
            'row2' => ['nested' => ['a' => 1, 'b' => 2]],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $yamlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedOutput = Yaml::dump($rows) . "\n";
        $this->assertEquals($expectedOutput, $yamlOutput);
    }
}
