<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ContextChoiceTest extends TestCase
{
    /** @dataProvider contextChoices */
    public function testContextChoices(string $class, array $choices, string $answer, ?string $expected): void
    {
        $command = new $class();
        $input = new ArrayInput([]);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $answer);
        rewind($stream);
        $input->setStream($stream);
        $method = new \ReflectionMethod(AbstractManagementCommand::class, 'askContext');
        if ($expected === null) {
            $this->expectException(\Exception::class);
        }
        try {
            $this->assertSame($expected, $method->invoke($command, $input, new BufferedOutput(), 'Context', $choices));
        } finally {
            fclose($stream);
        }
    }

    public static function contextChoices(): array
    {
        $cases = [
            'entity number' => [['catalog_category' => 'catalog_category (#3)', 'catalog_product' => 'catalog_product (#4)'], "2\n", 'catalog_product'],
            'entity code' => [['catalog_product' => 'catalog_product (#4)'], "catalog_product\n", 'catalog_product'],
            'completed entity label' => [['catalog_product' => 'catalog_product (#4)'], "catalog_product (#4)\n", 'catalog_product'],
            'incomplete entity name' => [['catalog_product' => 'catalog_product (#4)'], "catalog\n", null],
            'set name' => [[42 => 'Default (#42)'], "Default\n", '42'],
            'completed set label' => [[42 => 'Default (#42)'], "Default (#42)\n", '42'],
            'group name' => [[107 => 'Product Details (#107)'], "Product Details\n", '107'],
            'completed group label' => [[107 => 'Product Details (#107)'], "Product Details (#107)\n", '107'],
            'duplicate names' => [[42 => 'Default (#42)', 107 => 'Default (#107)'], "Default\n", null],
            'duplicate name with ID' => [[42 => 'Default (#42)', 107 => 'Default (#107)'], "Default (#107)\n", '107'],
            'duplicate labels' => [[42 => 'Duplicate', 107 => 'Duplicate'], "Duplicate\n", null],
            'ordinal with duplicate labels' => [[42 => 'Duplicate', 107 => 'Duplicate'], "2\n", '107'],
            'record ID is not an ordinal' => [[42 => 'Default (#42)'], "42\n", null],
            'numeric name is not an ordinal' => [[42 => '107 (#42)'], "107\n", null],
            'formatted label' => [[42 => '<info>Details</info> (#42)'], "<info>Details</info> (#42)\n", '42'],
            'unknown name' => [[42 => 'Default (#42)'], "Missing\n", null],
            'cancel number' => [[42 => 'Default (#42)'], "0\n", null],
            'cancel label' => [[42 => 'Default (#42)'], "Cancel (no changes)\n", null],
            'enter cancels' => [[42 => 'Default (#42)'], "\n", null],
            'EOF cancels' => [[42 => 'Default (#42)'], '', null],
        ];
        $result = [];
        foreach ([Attribute\CreateCommand::class, AttributeGroup\CreateCommand::class, AttributeGroup\ListCommand::class,
            AttributeGroup\ViewCommand::class, AttributeGroup\DeleteCommand::class] as $class) {
            foreach ($cases as $name => $case) {
                $result[$class . ': ' . $name] = [$class, ...$case];
            }
        }
        return $result;
    }
}
