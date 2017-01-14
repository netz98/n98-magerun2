<?php

namespace N98\Magento\Command\Developer\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\Write;

use N98\Magento\Application;
use N98\Magento\Command\TestCase;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ClearCommandTest extends TestCase
{

    /**
     * @param MockObject|ClearCommand $sut
     * @param array $params
     * @return void
     */
    protected function mockedCommandExecute($sut, $params)
    {
        /** @var MockObject|Application $application */
        $application = $this->getApplication();
        $application->add($sut);

        /** @var Command $command */
        $command = $application->find($params['command']);

        /** @var CommandTester $commandTester */
        $commandTester = new CommandTester($command);
        $commandTester->execute($params);
    }

    /**
     * @return array
     */
    public function dataProviderGetDirectoryWrite()
    {
        return [
            ['existing-directory', true],
            ['non-existing-directory', false]
        ];
    }

    /**
     * @param string $path
     * @param bool $expected
     * @return void
     * @dataProvider dataProviderGetDirectoryWrite
     */
    public function testGetDirectoryWrite($path, $expected)
    {
        /** @var MockObject|Write $writeMock */
        $writeMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAbsolutePath', 'isExist'])
            ->getMock();
        $writeMock
            ->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn($path);
        $writeMock
            ->expects($this->once())
            ->method('isExist')
            ->willReturn($expected);

        /** @var MockObject|Filesystem $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();
        $filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($writeMock);

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilesystem'])
            ->getMock();
        $sut
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);

        $actual = $sut->getDirectoryWrite('some-code');
        $this->assertEquals($expected, ($actual instanceof Write));
    }

    /**
     * @return array
     */
    public function dataProviderFindThemePaths()
    {
        return [
            [
                'Magento/luma',
                ['var/view_preprocessed/css/frontend/Magento/luma'],
                1,
                ['var/view_preprocessed/css/frontend/Magento/luma']
            ],
            [
                'Magento/luma',
                ['var/view_preprocessed/css/frontend/Magento/Luma'],
                0,
                []
            ],
            [
                'Magento/luma',
                ['var/view_preprocessed/css/frontend/Magento/lumas'],
                0,
                []
            ],
            [
                'Magento/luma',
                ['var/view_preprocessed/css/frontend/Magento/luma/en_US'],
                0,
                []
            ],
        ];
    }

    /**
     * @param string $theme
     * @param array $entries
     * @param bool $dirChecks
     * @param array $expected
     * @return void
     * @dataProvider dataProviderFindThemePaths
     */
    public function testFindThemePaths($theme, $entries, $dirChecks, $expected)
    {
        /** @var MockObject|Read $readMock */
        $readMock = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['readRecursively', 'isDirectory'])
            ->getMock();
        $readMock
            ->expects($this->once())
            ->method('readRecursively')
            ->willReturn($entries);
        $readMock
            ->expects($this->exactly($dirChecks))
            ->method('isDirectory')
            ->willReturn(true);

        /** @var MockObject|Filesystem $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryRead'])
            ->getMock();
        $filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($readMock);

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilesystem'])
            ->getMock();
        $sut
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);

        $actual = $sut->findThemePaths($theme, 'some-code');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function testDeleteDirectorySucceeds()
    {
        /** @var MockObject|Write $writeMock */
        $writeMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'getAbsolutePath'])
            ->getMock();
        $writeMock
            ->expects($this->once())
            ->method('delete');
        $writeMock
            ->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/home/acme/');

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilesystem'])
            ->getMock();

        $sut->deleteDirectory($writeMock, 'foo');
        $messages = $sut->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertContains('/home/acme/foo deleted', strip_tags($messages[0]));
    }

    /**
     * @return void
     */
    public function testDeleteDirectoryFails()
    {
        /** @var MockObject|Write $writeMock */
        $writeMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();
        $writeMock
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('whatever'));

        /** @var MockObject|Output $outputMock */
        $outputMock = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVerbosity'])
            ->getMockForAbstractClass();
        $outputMock
            ->expects($this->once())
            ->method('getVerbosity')
            ->willReturn(Output::VERBOSITY_VERBOSE);

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilesystem', 'getOutput'])
            ->getMock();
        $sut
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn($outputMock);

        $sut->deleteDirectory($writeMock, 'non-existent-directory');
        $messages = $sut->getMessages();
        $this->assertEquals(2, count($messages));
        $this->assertContains('whatever', strip_tags($messages[0]));
        $this->assertContains('#0 ', strip_tags($messages[1]));
    }

    /**
     * @return void
     */
    public function testEmptyDirectory()
    {
        /** @var MockObject|Write $writeMock */
        $writeMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['search'])
            ->getMock();
        $writeMock
            ->expects($this->once())
            ->method('search')
            ->with('*')
            ->willReturn([
                '.',
                '..',
                'foo',
                'bar'
            ]);

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilesystem', 'getDirectoryWrite', 'deleteDirectory'])
            ->getMock();
        $sut
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($writeMock);
        $sut
            ->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->with($this->isInstanceOf(Write::class), $this->isType('string'));

        $sut->emptyDirectory('some-code');
    }

    /**
     * @return void
     */
    public function testDeleteThemeDirectories()
    {
        /** @var MockObject|Write $writeMock */
        $writeMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getFilesystem',
                'getDirectoryWrite',
                'findThemePaths',
                'deleteDirectory'
            ])
            ->getMock();
        $sut
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($writeMock);
        $sut
            ->expects($this->once())
            ->method('findThemePaths')
            ->willReturn([
                'var/view_preprocessed/css/frontend/Magento/Luma',
                'var/view_preprocessed/source/frontend/Magento/Luma'
            ]);
        $sut
            ->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->with($this->isInstanceOf(Write::class), $this->isType('string'));

        $sut->deleteThemeDirectories('Magento/luma', 'some-code');
    }

    /**
     * @return array
     */
    public function dataProviderClearThemes()
    {
        return [
            [['command' => 'dev:asset:clear', '--theme' => ['Magento/luma']]],
            [['command' => 'dev:asset:clear', '--theme' => ['Magento/luma', 'Magento/backend']]]
        ];
    }

    /**
     * @param array $params
     * @return void
     * @dataProvider dataProviderClearThemes
     */
    public function testClearThemes($params)
    {
        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->setMethods(['deleteThemeDirectories'])
            ->getMock();
        $sut
            ->expects($this->exactly(2 * count($params['--theme'])))
            ->method('deleteThemeDirectories');

        $this->mockedCommandExecute($sut, $params);
    }

    /**
     * @return void
     */
    public function testClearAllThemes()
    {
        $params = ['command' => 'dev:asset:clear'];

        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->setMethods(['emptyDirectory'])
            ->getMock();
        $sut
            ->expects($this->exactly(2))
            ->method('emptyDirectory');

        $this->mockedCommandExecute($sut, $params);
    }

    /**
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [['command' => 'dev:asset:clear', '--theme' => ['Magento/luma']], 'clearThemes'],
            [['command' => 'dev:asset:clear'], 'clearAllThemes']
        ];
    }

    /**
     * @param array $params
     * @param string $method
     * @return void
     * @dataProvider dataProviderExecute
     */
    public function testExecute($params, $method)
    {
        /** @var MockObject|ClearCommand $sut */
        $sut = $this->getMockBuilder(ClearCommand::class)
            ->setMethods([$method])
            ->getMock();
        $sut
            ->expects($this->once())
            ->method($method);

        $this->mockedCommandExecute($sut, $params);
    }

}
