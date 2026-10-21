<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Preference;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class ConflictDetectorTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/magerun-preferences-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->directory);
    }

    private function write(string $module, string $scope, string $xml): string
    {
        $path = $this->directory . '/' . $module;
        $directory = $path . '/etc' . ($scope === 'global' ? '' : '/' . $scope);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory . '/di.xml', $xml);

        return $path;
    }

    private function xml(string ...$types): string
    {
        $xml = "<config>\n";
        foreach ($types as $type) {
            $xml .= '<preference for="Example\Target" type="' . $type . '"/>' . "\n";
        }

        return $xml . '</config>';
    }

    public function testReportsEveryContributingSourceDeterministically(): void
    {
        $paths = [
            'Example_Z' => $this->write('Example_Z', 'global', $this->xml('Example\Z', 'Example\Z')),
            'Example_A' => $this->write('Example_A', 'global', $this->xml('Example\A')),
        ];
        $detector = new ConflictDetector();
        $rows = $detector->detect($paths);
        $this->assertSame($rows, $detector->detect(array_reverse($paths, true)));
        $this->assertSame(['Example\A', 'Example\Z', 'Example\Z'], array_column($rows, 'type'));
        $this->assertSame(['Example_A', 'Example_Z', 'Example_Z'], array_column($rows, 'module'));
        $this->assertSame([2, 2, 3], array_column($rows, 'line'));
        $this->assertSame($paths['Example_A'] . '/etc/di.xml', $rows[0]['file']);
        $this->assertSame('Example\Target', $rows[0]['target']);
        $this->assertSame('global', $rows[0]['scope']);
    }

    public function testIdenticalRepeatedDeclarationsAreNotConflicts(): void
    {
        $paths = [
            'Example_A' => $this->write('Example_A', 'global', $this->xml('Example\Same', 'Example\Same')),
            'Example_B' => $this->write('Example_B', 'global', $this->xml('Example\Same')),
        ];
        $this->assertSame([], (new ConflictDetector())->detect($paths));
    }

    public function testGlobalAndIndependentAreaOverridesDoNotConflict(): void
    {
        $path = $this->write('Example_A', 'global', $this->xml('Example\Global'));
        $this->write('Example_A', 'frontend', $this->xml('Example\Frontend'));
        $this->write('Example_A', 'adminhtml', $this->xml('Example\Admin'));
        $this->assertSame([], (new ConflictDetector())->detect(['Example_A' => $path]));
    }

    public function testSameFileConflictsAndCustomAreasAreDetectedAndFiltered(): void
    {
        $path = $this->write('Example_A', 'custom_area', $this->xml('Example\B', 'Example\A'));
        $this->write('Example_A', 'global', $this->xml('Example\D', 'Example\C'));
        $this->write('Example_A', 'frontend', $this->xml('Example\Override'));
        $detector = new ConflictDetector();
        $paths = ['Example_A' => $path];
        $this->assertSame(['custom_area', 'custom_area', 'global', 'global'], array_column($detector->detect($paths), 'scope'));
        $this->assertCount(2, $detector->detect($paths, 'global'));
        $this->assertCount(2, $detector->detect($paths, 'custom_area'));
        $this->assertSame([], $detector->detect($paths, 'frontend'));
        $this->assertSame([], $detector->detect($paths, 'unknown_area'));
    }

    public function testOnlyDirectPreferencesAndSuppliedModulesAreInspected(): void
    {
        $path = $this->write('Enabled', 'global', '<config><type name="Example"><preference for="X" type="A"/><preference for="X" type="B"/></type></config>');
        $this->write('Disabled', 'global', $this->xml('Example\A', 'Example\B'));
        $this->assertSame([], (new ConflictDetector())->detect(['Enabled' => $path]));
        $this->assertSame([], (new ConflictDetector())->detect([]));
    }

    /**
     * @dataProvider invalidXmlProvider
     */
    public function testInvalidXmlFailsRatherThanReportingNoConflicts(string $xml): void
    {
        $path = $this->write('Example_A', 'global', $xml);
        $this->expectException(RuntimeException::class);
        (new ConflictDetector())->detect(['Example_A' => $path]);
    }

    public function invalidXmlProvider(): array
    {
        return [
            'malformed' => ['<config>'],
            'empty document' => [''],
            'wrong root' => ['<other/>'],
            'missing target' => ['<config><preference type="Example"/></config>'],
            'missing type' => ['<config><preference for="Example"/></config>'],
            'empty attribute' => ['<config><preference for="Example" type=" "/></config>'],
            'external entity' => ['<!DOCTYPE config [<!ENTITY secret SYSTEM "file:///must-not-be-read">]><config><preference for="Example" type="&secret;"/></config>'],
            'external dtd' => ['<!DOCTYPE config SYSTEM "https://invalid.example/di.dtd"><config/>'],
        ];
    }

    public function testLibxmlErrorModeIsRestoredAfterFailure(): void
    {
        $previous = libxml_use_internal_errors(false);
        try {
            $path = $this->write('Example_A', 'global', '<config>');
            try {
                (new ConflictDetector())->detect(['Example_A' => $path]);
                $this->fail('Malformed XML must fail');
            } catch (RuntimeException $exception) {
                $this->assertFalse(libxml_use_internal_errors());
            }
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    public function testMissingModuleDirectoryFails(): void
    {
        $this->expectException(RuntimeException::class);
        (new ConflictDetector())->detect(['Missing' => $this->directory . '/missing']);
    }

    public function testUnreadableSelectedFileFails(): void
    {
        $path = $this->write('Example_A', 'global', '<config/>');
        $file = $path . '/etc/di.xml';
        chmod($file, 0000);
        try {
            if (is_readable($file)) {
                $this->markTestSkipped('Current user can read files without permission bits');
            }
            $this->expectException(RuntimeException::class);
            (new ConflictDetector())->detect(['Example_A' => $path]);
        } finally {
            chmod($file, 0600);
        }
    }

    public function testTargetsAreGroupedAndSortedIndependently(): void
    {
        $xml = '<config><preference for="Z" type="B"/><preference for="A" type="B"/>'
            . '<preference for="Uncontested" type="Only"/><preference for="Z" type="A"/>'
            . '<preference for="A" type="A"/></config>';
        $path = $this->write('Example_[A]', 'global', $xml);
        $before = file_get_contents($path . '/etc/di.xml');
        $rows = (new ConflictDetector())->detect(['Example_A' => $path]);
        $this->assertSame(['A', 'A', 'Z', 'Z'], array_column($rows, 'target'));
        $this->assertSame(['A', 'B', 'A', 'B'], array_column($rows, 'type'));
        $this->assertSame($before, file_get_contents($path . '/etc/di.xml'));
    }

    public function testAreaCannotEscapeTheModuleDirectory(): void
    {
        $this->expectException(RuntimeException::class);
        (new ConflictDetector())->detect([], '../outside');
    }
}
