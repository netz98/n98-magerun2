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

class XmlRendererTest extends TestCase
{
    public function testRender()
    {
        $renderer = new XmlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'val1', 'col2' => 'val2'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $xmlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<table>
  <row>
    <col1>val1</col1>
    <col2>val2</col2>
  </row>
</table>
XML;
        $this->assertXmlStringEqualsXmlString($expectedXml, $xmlOutput);
    }

    public function testRenderEmptyRows()
    {
        $renderer = new XmlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [];

        $renderer->render($output, $rows);

        rewind($stream);
        $xmlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<table></table>
XML;
        $this->assertXmlStringEqualsXmlString($expectedXml, $xmlOutput);
    }

    public function testRenderSanitizedKeys()
    {
        $renderer = new XmlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col 1' => 'val1', 'col-2' => 'val2', 'col_3' => 'val3'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $xmlOutput = stream_get_contents($stream);
        fclose($stream);

        // 'col 1' -> 'col_1' (space replaced)
        // 'col-2' -> 'col_2' (dash replaced)
        // 'col_3' -> 'col_3' (underscore replaced by underscore because it matches [^A-Za-z0-9])

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<table>
  <row>
    <col_1>val1</col_1>
    <col_2>val2</col_2>
    <col_3>val3</col_3>
  </row>
</table>
XML;
        $this->assertXmlStringEqualsXmlString($expectedXml, $xmlOutput);
    }
}
