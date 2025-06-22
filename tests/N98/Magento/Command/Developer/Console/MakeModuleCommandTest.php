<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\Developer\Console\MakeModuleCommand;
use N98\Magento\Command\Developer\Console\Structure\ModuleNameStructure;
use N98\Magento\Command\Developer\Console\TestCase;
use N98\Magento\MagerunApplication;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\ModuleListInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\MockObject\MockObject;

class MakeModuleCommandTest extends TestCase
{
    /** @var MakeModuleCommand|MockObject */
    private $command;

    /** @var CommandTester */
    private $commandTester;

    /** @var MagerunApplication|MockObject */
    private $applicationMock;

    /** @var Filesystem|MockObject */
    private $filesystemMock;

    /** @var WriteInterface|MockObject */
    private $directoryWriteMock;

    /** @var ModuleListInterface|MockObject */
    private $moduleListMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationMock = $this->getMockBuilder(MagerunApplication::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryWriteMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock->method('getDirectoryWrite')->willReturn($this->directoryWriteMock);

        $this->moduleListMock = $this->getMockBuilder(ModuleListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->applicationMock->method('getMagentoRootFolder')->willReturn('/var/www/magento');

        $config = [
            'commands' => [
                MakeModuleCommand::class => [
                    'defaultModulesBaseDir' => 'app/code',
                ],
            ],
        ];
        $this->applicationMock->method('getConfig')->willReturn($config);

        $this->command = new MakeModuleCommand();
        $this->command->setApplication($this->applicationMock);

        // Mock the get() method to return our mocks
        $this->command->setService(Filesystem::class, $this->filesystemMock);
        $this->command->setService(ModuleListInterface::class, $this->moduleListMock);
        // Mock other services as needed for full execution, or mock methods on the command itself

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecutePromptsForModuleNameWhenMissing()
    {
        $this->moduleListMock->method('getOne')->willReturn(null); // Simulate module does not exist

        // Mock QuestionHelper
        $questionHelperMock = $this->getMockBuilder(QuestionHelper::class)
            ->setMethods(['ask'])
            ->getMock();

        $expectedModuleName = 'Test_ModulePrompt';
        $questionHelperMock->expects($this->once())
            ->method('ask')
            ->willReturn($expectedModuleName);

        $this->command->getHelperSet()->set($questionHelperMock, 'question');

        // Mock methods that perform file operations or other side effects
        $this->directoryWriteMock->method('writeFile')->willReturn(true);
        $this->directoryWriteMock->method('create')->willReturn(true);

        // Mock the methods called after module creation to prevent further execution
        $mockedCommand = $this->getMockBuilder(MakeModuleCommand::class)
            ->onlyMethods(['activateNewModuleInSystem', 'cleanClassCache', 'changeToNewModule', 'includeRegistrationFile', 'getMagerunApplication', 'get', 'getHelper', 'create'])
            ->getMock();

        $mockedCommand->method('getMagerunApplication')->willReturn($this->applicationMock);
        $mockedCommand->method('get')->willReturnMap([
            [Filesystem::class, $this->filesystemMock],
            [ModuleListInterface::class, $this->moduleListMock],
        ]);
        $mockedCommand->method('create')->willReturn($this->moduleListMock); // Assuming create is for ModuleListInterface

        // Configure QuestionHelper mock for the mocked command
        $mockedCommand->getHelperSet()->set($questionHelperMock, 'question');


        $commandTester = new CommandTester($mockedCommand);
        $commandTester->execute([]); // No 'modulename' argument

        // We can't directly assert the internal $moduleName variable.
        // Instead, we should check if the writeFile (or a similar method) was called with the correct name.
        // This requires more specific mocking of createRegistrationFile or similar.
        // For now, we've asserted that `ask` is called.
        // A more complete test would verify the module files are "written" with $expectedModuleName.

        $this->assertStringContainsString("created new module {$expectedModuleName}", $commandTester->getDisplay());
    }

    public function testHelpMessageForModulesBaseDir()
    {
        $this->applicationMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn([
                'commands' => [
                    MakeModuleCommand::class => [
                        'defaultModulesBaseDir' => 'app/code',
                    ],
                ],
            ]);

        // Re-initialize command with potentially new application mock behavior for this test
        $command = new MakeModuleCommand();
        $command->setApplication($this->applicationMock);

        $option = $command->getDefinition()->getOption('modules-base-dir');
        $this->assertStringContainsString('Default is app/code', $option->getDescription());

        // Test with a different configured path
        $this->applicationMock = $this->getMockBuilder(MagerunApplication::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->applicationMock->method('getMagentoRootFolder')->willReturn('/var/www/magento');
        $this->applicationMock->method('getConfig')->willReturn([
            'commands' => [
                MakeModuleCommand::class => [
                    'defaultModulesBaseDir' => 'vendor/custom_modules',
                ],
            ],
        ]);

        $commandWithDifferentConfig = new MakeModuleCommand();
        $commandWithDifferentConfig->setApplication($this->applicationMock);
        $option = $commandWithDifferentConfig->getDefinition()->getOption('modules-base-dir');
        $this->assertStringContainsString('Default is vendor/custom_modules', $option->getDescription());
    }
}
