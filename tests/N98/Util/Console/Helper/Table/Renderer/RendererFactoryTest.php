<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

class RendererFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \N98\Util\Console\Helper\Table\Renderer\RendererFactory::create
     */
    public function testCreate()
    {
        $renderFactory = new RendererFactory();

        $csv = $renderFactory->create('csv');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\CsvRenderer', $csv);

        $tsv = $renderFactory->create('tsv');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\TsvRenderer', $tsv);

        $json = $renderFactory->create('json');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\JsonRenderer', $json);

        $jsonArray = $renderFactory->create('json_array');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\JsonArrayRenderer', $jsonArray);

        $jsonLines = $renderFactory->create('jsonl');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\JsonLinesRenderer', $jsonLines);

        $yaml = $renderFactory->create('yaml');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\YamlRenderer', $yaml);
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\YamlRenderer', $renderFactory->create('yml'));

        $markdown = $renderFactory->create('markdown');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\MarkdownRenderer', $markdown);
        $this->assertInstanceOf(
            'N98\Util\Console\Helper\Table\Renderer\MarkdownRenderer',
            $renderFactory->create('md')
        );

        $this->assertInstanceOf(
            'N98\Util\Console\Helper\Table\Renderer\JsonLinesRenderer',
            $renderFactory->create('ndjson')
        );

        $xml = $renderFactory->create('xml');
        $this->assertInstanceOf('N98\Util\Console\Helper\Table\Renderer\XmlRenderer', $xml);

        $invalidFormat = $renderFactory->create('invalid_format');
        $this->assertFalse($invalidFormat);
    }

    /**
     * @covers \N98\Util\Console\Helper\Table\Renderer\RendererFactory::getFormats
     */
    public function testGetFormats()
    {
        $formats = RendererFactory::getFormats();
        $this->assertIsArray($formats);
        $this->assertContains('csv', $formats);
        $this->assertContains('json', $formats);
        $this->assertContains('json_array', $formats);
        $this->assertContains('tsv', $formats);
        $this->assertContains('jsonl', $formats);
        $this->assertContains('yaml', $formats);
        $this->assertContains('markdown', $formats);
        $this->assertContains('xml', $formats);
        $this->assertCount(8, $formats);
    }
}
