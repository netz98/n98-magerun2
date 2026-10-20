<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav;

use Magento\Eav\Setup\EavSetup;
use N98\Magento\Command\TestCase;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;

class FormatTest extends TestCase
{
    private function command(bool $view, bool $empty): array
    {
        $this->getApplication();
        $setup = $this->createMock(EavSetup::class);
        $command = $this->getMockBuilder($view ? AttributeGroup\ViewCommand::class : AttributeGroup\ListCommand::class)
            ->onlyMethods(['initializeManagement', 'context', 'entityChoices', 'setChoices', 'entityId', 'attributeSet', 'attributeGroup', 'groupRows', 'assignedAttributes'])
            ->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('context')->willReturn([$setup, 4, 107]);
        $command->method('entityChoices')->willReturn(['customer' => 'Customer', 'catalog_product' => 'Product']);
        $command->method('setChoices')->with(4)->willReturn([42 => 'Duplicate', 107 => 'Duplicate']);
        $command->method('entityId')->with('catalog_product')->willReturn(4);
        $command->method('attributeSet')->willReturn(['attribute_set_id' => 107]);
        $group = ['attribute_group_id' => 907, 'attribute_group_name' => 'Group, "quoted" & <info>', 'attribute_set_id' => 107, 'sort_order' => 20, 'default_id' => 0];
        $command->method('attributeGroup')->willReturn($group);
        $other = array_replace($group, ['attribute_group_id' => 501, 'sort_order' => 10]);
        $command->method('groupRows')->with(107)->willReturn(!$view && $empty ? [] : [$other, $group]);
        $attribute = ['attribute_id' => 301, 'sort_order' => 5, 'attribute_code' => 'example', 'frontend_label' => 'Label, "quoted" & <info>'];
        $command->method('assignedAttributes')->with(107, 907)->willReturn($empty ? [] : [$attribute]);
        $this->getApplication()->add($command);
        if ($view) {
            $rows = [];
            foreach ($group as $key => $value) {
                $rows[] = [$key, $value];
            }
            if (!$empty) {
                $rows[] = ['attribute.example', 'ID: 301; label: Label, "quoted" & <info>; sort order: 5'];
            }
            return [$command, ['property', 'value'], $rows];
        }
        $rows = $empty ? [] : [[501, $group['attribute_group_name'], 107, 10, 0], [907, $group['attribute_group_name'], 107, 20, 0]];
        return [$command, ['id', 'name', 'attribute_set_id', 'sort_order', 'default_id'], $rows];
    }

    /** @dataProvider formatCases */
    public function testSharedFormatsAndSeparatePromptOutput(?string $format, bool $view, bool $empty, bool $interactive): void
    {
        [$command, $headers, $rows] = $this->command($view, $empty);
        $option = $command->getDefinition()->getOption('format');
        $this->assertTrue($option->isValueOptional());
        $this->assertNull($option->getDefault());
        $arguments = $interactive ? [] : ['entityType' => 'catalog_product', 'attributeSetId' => '107'];
        if ($view && !$interactive) {
            $arguments['attributeGroupId'] = '907';
        }
        if ($format !== null) {
            $arguments['--format'] = $format;
        }
        $tester = new CommandTester($command);
        $tester->setInputs($view ? ['2', '2', '2'] : ['2', '2']);
        $this->assertSame(0, $tester->execute($arguments, ['interactive' => $interactive, 'capture_stderr_separately' => true]));
        $this->assertSame('catalog_product', $tester->getInput()->getArgument('entityType'));
        $this->assertSame('107', $tester->getInput()->getArgument('attributeSetId'));
        if ($view) {
            $this->assertSame('907', $tester->getInput()->getArgument('attributeGroupId'));
        }
        $stream = fopen('php://memory', 'r+');
        try {
            // Compare against the actual shared renderer, not a new EAV serializer.
            (new TableHelper())->setHeaders($headers)->renderByFormat(new StreamOutput($stream), $rows, $format);
            rewind($stream);
            $this->assertSame(stream_get_contents($stream), $tester->getDisplay());
        } finally {
            fclose($stream);
        }
        if ($interactive) {
            $this->assertStringContainsString('Please select an entity type', $tester->getErrorOutput());
        } else {
            $this->assertSame('', $tester->getErrorOutput());
        }
        $this->assertStringNotContainsString('Please select', $tester->getDisplay());
        $normalized = ['yml' => 'yaml', 'md' => 'markdown', 'ndjson' => 'jsonl'][$format ?? ''] ?? $format;
        if (in_array($normalized, ['json', 'json_array'], true)) {
            $parsed = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(count($rows), $parsed);
            $this->assertStringStartsWith($normalized === 'json' ? '{' : '[', $tester->getDisplay());
        } elseif (in_array($normalized, ['csv', 'tsv'], true)) {
            $csv = fopen('php://memory', 'r+');
            fwrite($csv, $tester->getDisplay());
            rewind($csv);
            try {
                $first = fgetcsv($csv, 0, $normalized === 'csv' ? ',' : "\t", '"', '');
                $this->assertSame($rows ? $headers : false, $first);
                foreach ($rows as $row) {
                    $this->assertSame(array_map('strval', $row), fgetcsv($csv, 0, $normalized === 'csv' ? ',' : "\t", '"', ''));
                }
                $this->assertFalse(fgetcsv($csv, 0, $normalized === 'csv' ? ',' : "\t", '"', ''));
            } finally {
                fclose($csv);
            }
        } elseif ($normalized === 'xml') {
            $xml = simplexml_load_string($tester->getDisplay());
            $this->assertNotFalse($xml);
            $this->assertCount(count($rows), $xml->row);
        } elseif ($normalized === 'yaml') {
            $this->assertCount(count($rows), \Symfony\Component\Yaml\Yaml::parse($tester->getDisplay()));
        } elseif ($normalized === 'jsonl') {
            $lines = array_filter(explode("\n", $tester->getDisplay()));
            $this->assertCount(count($rows), $lines);
            foreach ($lines as $line) {
                $this->assertIsArray(json_decode($line, true, 512, JSON_THROW_ON_ERROR));
            }
        }
    }

    public static function formatCases(): array
    {
        $cases = [];
        foreach (array_merge([null], RendererFactory::getFormats(), ['yml', 'md', 'ndjson']) as $format) {
            foreach ([false, true] as $view) {
                foreach ([false, true] as $empty) {
                    foreach ([false, true] as $interactive) {
                        $cases[] = [$format, $view, $empty, $interactive];
                    }
                }
            }
        }
        return $cases;
    }

    /** @dataProvider invalidFormats */
    public function testUnknownFormatIsRejectedWithoutDataOutput(bool $view, string $format): void
    {
        [$command] = $this->command($view, true);
        $tester = new CommandTester($command);
        $arguments = ['entityType' => 'catalog_product', 'attributeSetId' => '107', '--format' => $format];
        if ($view) {
            $arguments['attributeGroupId'] = '907';
        }
        try {
            $tester->execute($arguments, ['interactive' => false, 'capture_stderr_separately' => true]);
            $this->fail('Unknown formats must fail.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Unknown output format', $exception->getMessage());
        }
        $this->assertSame('', $tester->getDisplay());
    }

    public static function invalidFormats(): array
    {
        return [[false, 'invalid'], [true, 'invalid'], [false, ''], [true, ''], [false, 'table'], [true, 'table']];
    }

    public function testMutatingCommandsDoNotDeclareTableFormats(): void
    {
        foreach ([new Attribute\CreateCommand(), new AttributeGroup\CreateCommand(), new AttributeGroup\DeleteCommand()] as $command) {
            $this->assertFalse($command->getDefinition()->hasOption('format'));
        }
    }
}
