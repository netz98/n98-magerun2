<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Observer;

use N98\Magento\Command\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\TableSeparator;

// Define dummy interfaces/classes if they don't exist
if (!interface_exists('Magento\Framework\ObjectManagerInterface')) {
    eval('namespace Magento\Framework; interface ObjectManagerInterface {
        public function get($type);
        public function create($type, array $arguments = []);
        public function configure(array $configuration);
    }');
}

if (!class_exists('Magento\Framework\Event\Config\Reader')) {
    eval('namespace Magento\Framework\Event\Config; class Reader {
        public function read($scope) {}
    }');
}

class ListCommandUnitTest extends TestCase
{
    /**
     * @var ListCommand
     */
    private $command;

    /**
     * @var MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        // Skip parent setup which tries to init real application
        $this->command = new ListCommand();

        // Setup Helpers
        $helperSet = new \Symfony\Component\Console\Helper\HelperSet([
            new \N98\Util\Console\Helper\TableHelper(),
            new \Symfony\Component\Console\Helper\FormatterHelper(),
            new \Symfony\Component\Console\Helper\QuestionHelper(),
        ]);
        $this->command->setHelperSet($helperSet);

        // AbstractMagentoCommand expects 'command' argument to be present
        $this->command->getDefinition()->addArgument(
            new \Symfony\Component\Console\Input\InputArgument('command', \Symfony\Component\Console\Input\InputArgument::OPTIONAL)
        );
    }

    public function testExecuteWithSeparators()
    {
        // Mock Application
        $applicationMock = $this->getMockBuilder(\N98\Magento\Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Ensure Application mock returns our HelperSet
        $applicationMock->method('getHelperSet')->willReturn($this->command->getHelperSet());

        $applicationMock->method('initMagento')->willReturn(true);
        $applicationMock->method('detectMagento')->willReturn(true);
        $applicationMock->method('getMagentoRootFolder')->willReturn('/tmp');
        $applicationMock->method('isMagentoEnterprise')->willReturn(false);
        $applicationMock->method('getMagentoMajorVersion')->willReturn(2);

        // Mock ObjectManager
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMock();

        $applicationMock->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->command->setApplication($applicationMock);

        // Mock Config Reader
        $configReaderMock = $this->getMockBuilder('Magento\Framework\Event\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $configReaderMock->method('read')->willReturn([
            'event_one' => [
                'observer_a' => ['instance' => 'ClassA', 'name' => 'methodA'],
            ],
            'event_two' => [
                'observer_b' => ['instance' => 'ClassB', 'name' => 'methodB'],
            ],
            'event_three' => [
                'observer_c' => ['instance' => 'ClassC', 'name' => 'methodC'],
            ]
        ]);

        $this->objectManagerMock->method('get')
            ->with('\Magento\Framework\Event\Config\Reader')
            ->willReturn($configReaderMock);

        $commandTester = new CommandTester($this->command);

        $commandTester->execute(['area' => 'global']);

        $output = $commandTester->getDisplay();

        // Count occurrences of the separator line pattern "+---"
        // It should appear 2 (borders) + 1 (header sep) + 2 (inner separators) = 5 times

        $separatorCount = substr_count($output, '+---');
        $this->assertGreaterThanOrEqual(5, $separatorCount, 'Output should contain separators between events');
    }

    public function testExecuteWithoutSeparatorsWhenFormatIsSet()
    {
        // Mock Application
        $applicationMock = $this->getMockBuilder(\N98\Magento\Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Ensure Application mock returns our HelperSet
        $applicationMock->method('getHelperSet')->willReturn($this->command->getHelperSet());

        $applicationMock->method('initMagento')->willReturn(true);
        $applicationMock->method('detectMagento')->willReturn(true);
        $applicationMock->method('getMagentoRootFolder')->willReturn('/tmp');
        $applicationMock->method('isMagentoEnterprise')->willReturn(false);
        $applicationMock->method('getMagentoMajorVersion')->willReturn(2);

        // Mock ObjectManager
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMock();

        $applicationMock->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->command->setApplication($applicationMock);

        // Mock Config Reader
        $configReaderMock = $this->getMockBuilder('Magento\Framework\Event\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $configReaderMock->method('read')->willReturn([
            'event_one' => [
                'observer_a' => ['instance' => 'ClassA', 'name' => 'methodA'],
            ],
            'event_two' => [
                'observer_b' => ['instance' => 'ClassB', 'name' => 'methodB'],
            ]
        ]);

        $this->objectManagerMock->method('get')
            ->with('\Magento\Framework\Event\Config\Reader')
            ->willReturn($configReaderMock);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['area' => 'global', '--format' => 'csv']);

        $output = $commandTester->getDisplay();

        // CSV should not contain table borders
        $this->assertStringNotContainsString('+---', $output, 'CSV output should not contain table separators');

        // Should contain raw data
        $this->assertStringContainsString('event_one,observer_a,ClassA::methodA', $output);
        $this->assertStringContainsString('event_two,observer_b,ClassB::methodB', $output);
    }
}
