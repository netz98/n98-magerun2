<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

use PHPUnit\Framework\TestCase;

class CreationMetadataTest extends TestCase
{
    private function options(array $overrides = []): array
    {
        return array_replace([
            'label' => 'Example', 'input' => 'text', 'required' => '0', 'unique' => '0',
            'visible' => '1', 'scope' => 'global', 'sort-order' => '0', 'option' => [], 'default' => null,
        ], $overrides);
    }

    public function testTextMetadataIsUserDefinedAndDoesNotAssignToEverySet(): void
    {
        $data = (new CreationMetadata())->build('example', $this->options(['default' => '0']));
        $this->assertSame('varchar', $data['type']);
        $this->assertSame('0', $data['default']);
        $this->assertTrue($data['user_defined']);
        $this->assertFalse($data['system']);
        $this->assertArrayNotHasKey('group', $data);
    }

    public function testMultiselectHasCoreBackendAndAdminOptions(): void
    {
        $data = (new CreationMetadata())->build('example', $this->options([
            'input' => 'multiselect', 'option' => ['Red', 'Green'], 'scope' => 'store',
        ]));
        $this->assertSame(['values' => ['Red', 'Green']], $data['option']);
        $this->assertSame('Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend', $data['backend']);
        $this->assertSame(0, $data['global']);
    }

    public function testAcceptsMaximumSignedSmallintSortOrder(): void
    {
        $data = (new CreationMetadata())->build('example', $this->options(['sort-order' => '32767']));
        $this->assertSame(32767, $data['sort_order']);
    }

    /** @dataProvider invalidMetadata */
    public function testRejectsInvalidMetadataBeforeMutation(string $code, array $options): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new CreationMetadata())->build($code, $this->options($options));
    }

    public static function invalidMetadata(): array
    {
        return [
            ['invalid-code', []], ['Uppercase', []], [str_repeat('a', 31), []],
            ['valid', ['label' => null]], ['valid', ['label' => "bad\nlabel"]],
            ['valid', ['input' => 'arbitrary_class']], ['valid', ['required' => 'yes']],
            ['valid', ['unique' => '-1']], ['valid', ['visible' => 'true']],
            ['valid', ['scope' => 'invalid']], ['valid', ['sort-order' => '-1']],
            ['valid', ['sort-order' => '32768']], ['valid', ['sort-order' => '65536']],
            ['valid', ['input' => 'select']],
            ['valid', ['input' => 'select', 'option' => ['Red', 'Red']]],
            ['valid', ['input' => 'select', 'option' => [' ']]],
            ['valid', ['option' => ['Red']]], ['valid', ['input' => 'boolean', 'default' => '2']],
            ['valid', ['input' => 'date', 'default' => 'yesterday']],
        ];
    }
}
