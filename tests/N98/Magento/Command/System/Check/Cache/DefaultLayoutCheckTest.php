<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\Cache;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleListInterface;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\TestCase;
use N98\Util\Console\Helper\TableHelper;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\BufferedOutput;

class DefaultLayoutCheckTest extends TestCase
{
    private ClassLoader $frameworkLoader;

    protected function setUp(): void
    {
        // Do not load Magento's dev dependencies over the running PHPUnit version.
        $this->frameworkLoader = new ClassLoader();
        $prefixes = require $this->getTestMagentoRoot() . '/vendor/composer/autoload_psr4.php';
        $this->frameworkLoader->addPsr4('Magento\\Framework\\', $prefixes['Magento\\Framework\\']);
        $this->frameworkLoader->register();
        vfsStream::setup('layouts');
    }

    protected function tearDown(): void
    {
        $this->frameworkLoader->unregister();
    }

    public function testReportsOnlyFalseBlockDeclarations(): void
    {
        $this->write('module/view/frontend/layout/default.xml', '<page><body>
            <!-- <block name="comment" cacheable="false"/> -->
            <block name="literal" cacheable="false"/>
            <block name="numeric" cacheable=" 0 "/>
            <block cacheable="false"/>
            <block name="enabled" cacheable="true"/>
            <block name="one" cacheable="1"/>
            <block name="implicit"/>
            <block name="legacy" cache="disable"/>
            <referenceBlock name="reference" cacheable="false"/>
        </body></page>');

        $results = $this->runCheck();
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertSame(Result::STATUS_ERROR, $result->getStatus());
            $this->assertStringContainsString('module/view/frontend/layout/default.xml:', $result->getMessage());
        }
        $this->assertStringContainsString('"literal"', $results[0]->getMessage());
        $this->assertStringContainsString('"numeric"', $results[1]->getMessage());
        $this->assertStringContainsString('"(unnamed)"', $results[2]->getMessage());
    }

    public function testDiscoveryIncludesBaseAndThemeOverridesButExcludesOtherAreasAndHandles(): void
    {
        foreach ([
            'module/view/base/layout/default.xml',
            'module/view/frontend/layout/default.xml',
            'theme/Test_Module/layout/default.xml',
            'theme/Test_Module/layout/override/base/default.xml',
            'theme/Test_Module/layout/override/theme/Vendor/parent/default.xml',
            'parent/Test_Module/layout/default.xml',
        ] as $path) {
            $this->write($path, '<page><body><block name="offender" cacheable="false"/></body></page>');
        }
        foreach ([
            'module/view/adminhtml/layout/default.xml',
            'module/view/frontend/layout/catalog_product_view.xml',
            'disabled/view/frontend/layout/default.xml',
            'theme/Disabled_Module/layout/default.xml',
            'theme/Test_Module/layout/checkout_index_index.xml',
            'theme/Test_Module/layout/unrelated/default.xml',
            'admin/Test_Module/layout/default.xml',
        ] as $path) {
            $this->write($path, '<page><body><block name="excluded" cacheable="false"/></body></page>');
        }

        $results = $this->runCheck();
        $this->assertCount(6, $results);
        foreach ($results as $result) {
            $this->assertSame(Result::STATUS_ERROR, $result->getStatus());
            $this->assertStringNotContainsString('excluded', $result->getMessage());
        }
    }

    public function testCleanScanAndXmlWarnings(): void
    {
        $this->write('module/view/frontend/layout/default.xml', '<page><body><block cacheable="true"/></body></page>');
        $results = $this->runCheck();
        $this->assertCount(1, $results);
        $this->assertSame(Result::STATUS_OK, $results[0]->getStatus());

        foreach (['', '<page>', '<!DOCTYPE page [<!ENTITY example "false">]><page/>'] as $xml) {
            $this->write('module/view/frontend/layout/default.xml', $xml);
            $results = $this->runCheck();
            $this->assertCount(1, $results);
            $this->assertSame(Result::STATUS_WARNING, $results[0]->getStatus());
            $this->assertStringContainsString('default.xml', $results[0]->getMessage());
        }
    }

    public function testUnreadableLayoutProducesWarningWithoutCorruptingJsonOutput(): void
    {
        $path = 'module/view/frontend/layout/default.xml';
        $this->write($path, '<page><body><block name="unreadable" cacheable="false"/></body></page>');
        chmod(vfsStream::url('layouts/' . $path), 0000);
        $this->assertFalse(is_readable(vfsStream::url('layouts/' . $path)));
        $this->expectOutputString('');

        $previous = libxml_use_internal_errors(false);
        try {
            $results = $this->runCheck();
            $this->assertFalse(libxml_use_internal_errors());
        } finally {
            libxml_use_internal_errors($previous);
        }
        $this->assertCount(1, $results);
        $this->assertSame(Result::STATUS_WARNING, $results[0]->getStatus());
        $this->assertStringContainsString($path, $results[0]->getMessage());

        $output = new BufferedOutput();
        $table = new TableHelper();
        $table->setHeaders(['Group', 'Message', 'Result']);
        $table->renderByFormat($output, [[
            $results[0]->getResultGroup(),
            $results[0]->getMessage(),
            $results[0]->getStatus(),
        ]], 'json');
        $decoded = json_decode($output->fetch(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(Result::STATUS_WARNING, $decoded[0]['Result']);
    }

    private function write(string $path, string $xml): void
    {
        $file = vfsStream::url('layouts/' . $path);
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $xml);
    }

    /** @return Result[] */
    private function runCheck(): array
    {
        $registrar = $this->createMock(ComponentRegistrar::class);
        $registrar->method('getPaths')->willReturnMap([
            [ComponentRegistrar::MODULE, [
                'Test_Module' => vfsStream::url('layouts/module'),
                'Disabled_Module' => vfsStream::url('layouts/disabled'),
            ]],
            [ComponentRegistrar::THEME, [
                'frontend/Vendor/theme' => vfsStream::url('layouts/theme'),
                'frontend/Vendor/parent' => vfsStream::url('layouts/parent'),
                'adminhtml/Vendor/admin' => vfsStream::url('layouts/admin'),
            ]],
        ]);
        $moduleList = $this->createMock(ModuleListInterface::class);
        $moduleList->method('getNames')->willReturn(['Test_Module']);
        $results = new ResultCollection();
        $results->setResultGroup('cache');
        (new DefaultLayoutCheck($registrar, $moduleList))->check($results);

        return iterator_to_array($results)['cache'];
    }
}
