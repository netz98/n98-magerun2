<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\Developer\Module\CreateCommand;
use N98\Magento\Command\SubCommand\ConfigBag;
use N98\Util\Console\Helper\TwigHelper;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class CreateReadmeFileTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testExecuteStandardMode()
    {
        $moduleDirectory = vfsStream::url('root/app/code/Vendor/Module');
        mkdir($moduleDirectory, 0777, true);

        $config = new ConfigBag([
            'isModmanMode' => false,
            'moduleDirectory' => $moduleDirectory,
            'twigVars' => ['foo' => 'bar']
        ]);

        $output = new BufferedOutput();
        $subCommand = new CreateReadmeFile();
        $subCommand->setConfig($config);
        $subCommand->setOutput($output);

        $twigHelper = $this->createMock(TwigHelper::class);
        $twigHelper->expects($this->once())
            ->method('render')
            ->with('dev/module/create/app/code/module/readme.md.twig', ['foo' => 'bar'])
            ->willReturn('Rendered Content');

        $command = $this->createMock(CreateCommand::class);
        $command->method('getHelper')
            ->with('twig')
            ->willReturn($twigHelper);

        $subCommand->setCommand($command);

        $subCommand->execute();

        $expectedFile = $moduleDirectory . '/readme.md';
        $this->assertFileExists($expectedFile);
        $this->assertEquals('Rendered Content', file_get_contents($expectedFile));
        $this->assertStringContainsString('Created file: ' . $expectedFile, $output->fetch());
    }

    public function testExecuteModmanMode()
    {
        $modmanRootFolder = vfsStream::url('root/Vendor_Module');
        mkdir($modmanRootFolder, 0777, true);

        $config = new ConfigBag([
            'isModmanMode' => true,
            'modmanRootFolder' => $modmanRootFolder,
            'twigVars' => ['foo' => 'bar']
        ]);

        $output = new BufferedOutput();
        $subCommand = new CreateReadmeFile();
        $subCommand->setConfig($config);
        $subCommand->setOutput($output);

        $twigHelper = $this->createMock(TwigHelper::class);
        $twigHelper->expects($this->once())
            ->method('render')
            ->with('dev/module/create/app/code/module/readme.md.twig', ['foo' => 'bar'])
            ->willReturn('Rendered Content Modman');

        $command = $this->createMock(CreateCommand::class);
        $command->method('getHelper')
            ->with('twig')
            ->willReturn($twigHelper);

        $subCommand->setCommand($command);

        $subCommand->execute();

        $expectedFile = $modmanRootFolder . '/readme.md';
        $this->assertFileExists($expectedFile);
        $this->assertEquals('Rendered Content Modman', file_get_contents($expectedFile));
        $this->assertStringContainsString('Created file: ' . $expectedFile, $output->fetch());
    }
}
