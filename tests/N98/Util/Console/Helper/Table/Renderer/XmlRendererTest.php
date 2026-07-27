<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

use N98\Util\Console\Theme;
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

    /**
     * A column whose name collides with a style tag registered on the formatter - `label` is one of
     * magerun's own, `info` one of Symfony's - must still be written as an element. Rendering
     * through the formatter used to swallow those tags and leave the cell's text bare, which is how
     * `eav:attribute:list --format=xml` came to emit an unwrapped "Weight".
     */
    public function testRenderKeysThatCollideWithStyleTags()
    {
        $renderer = new XmlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);
        Theme::apply($output);

        $rows = [
            ['code' => 'weight_type', 'label' => 'Dynamic Weight', 'info' => 'decimal'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $xmlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<table>
  <row>
    <code>weight_type</code>
    <label>Dynamic Weight</label>
    <info>decimal</info>
  </row>
</table>
XML;
        $this->assertXmlStringEqualsXmlString($expectedXml, $xmlOutput);
    }

    /**
     * Values are text, not markup: the characters XML reserves have to survive as data.
     */
    public function testRenderEscapesReservedCharactersInValues()
    {
        $renderer = new XmlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['col1' => 'Terms & Conditions', 'col2' => 'a < b > c', 'col3' => 'say "hi"'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $xmlOutput = stream_get_contents($stream);
        fclose($stream);

        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xmlOutput), 'renderer produced malformed XML');

        $row = $document->getElementsByTagName('row')->item(0);
        $this->assertSame('Terms & Conditions', $row->getElementsByTagName('col1')->item(0)->textContent);
        $this->assertSame('a < b > c', $row->getElementsByTagName('col2')->item(0)->textContent);
        $this->assertSame('say "hi"', $row->getElementsByTagName('col3')->item(0)->textContent);
    }

    /**
     * XML names may not start with a digit, so a numeric column header needs a prefix rather than
     * an exception out of createElement().
     */
    public function testRenderNumericKeys()
    {
        $renderer = new XmlRenderer();
        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        $rows = [
            ['val1', 'val2'],
        ];

        $renderer->render($output, $rows);

        rewind($stream);
        $xmlOutput = stream_get_contents($stream);
        fclose($stream);

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<table>
  <row>
    <_0>val1</_0>
    <_1>val2</_1>
  </row>
</table>
XML;
        $this->assertXmlStringEqualsXmlString($expectedXml, $xmlOutput);
    }
}
