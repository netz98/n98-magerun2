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

    /** @dataProvider delimitedRenderers */
    public function testQuotedHeadersAndValuesRoundTrip(string $class, string $delimiter): void
    {
        $rows = [
            ['Header, "quoted"' => "Value, \"quoted\"\twith tabs\nand newline", 'plain' => '<info> & Unicode ä'],
            ['Header, "quoted"' => '', 'plain' => "Backslash \\\" and quote \""],
        ];
        $stream = fopen('php://memory', 'r+');
        try {
            (new $class())->render(new StreamOutput($stream), $rows);
            rewind($stream);
            $this->assertStringContainsString('""quoted""', stream_get_contents($stream));
            rewind($stream);
            $this->assertSame(array_keys($rows[0]), fgetcsv($stream, 0, $delimiter, '"', ''));
            foreach ($rows as $row) {
                $this->assertSame(array_values($row), fgetcsv($stream, 0, $delimiter, '"', ''));
            }
            $this->assertFalse(fgetcsv($stream, 0, $delimiter, '"', ''));
        } finally {
            fclose($stream);
        }
    }

    /** @dataProvider delimitedRenderers */
    public function testEmptyDelimitedOutputStillHasNoHeader(string $class, string $delimiter): void
    {
        $stream = fopen('php://memory', 'r+');
        try {
            (new $class())->render(new StreamOutput($stream), []);
            rewind($stream);
            $this->assertSame('', stream_get_contents($stream));
        } finally {
            fclose($stream);
        }
    }

    public static function delimitedRenderers(): array
    {
        return [[CsvRenderer::class, ','], [TsvRenderer::class, "\t"]];
    }
}
