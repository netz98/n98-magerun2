<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InfoCommandTest extends TestCase
{
    public function testAttributeExceptionsAndErrorsUseTheSameFallbackOnRepeatedRuns()
    {
        $application = $this->getApplication();
        $frontend = $this->createMock(DefaultFrontend::class);
        $frontend->method('getLabel')->willReturn('Disable Automatic Group Change');
        $calls = 0;
        $frontend->expects($this->exactly(2))->method('getValue')->willReturnCallback(
            static function () use (&$calls) {
                if ($calls++ === 0) {
                    throw new \RuntimeException('Source model could not be created');
                }
                throw new \Error('Call to a member function getOptionText() on string');
            }
        );
        $attribute = $this->createMock(Attribute::class);
        $attribute->method('getFrontend')->willReturn($frontend);
        $resource = $this->createMock(CustomerResource::class);
        $resource->method('getAttribute')->with('disable_auto_group_change')->willReturn($attribute);
        $customer = $this->createMock(Customer::class);
        $customer->method('getResource')->willReturn($resource);
        $customer->method('toArray')->willReturn([
            'disable_auto_group_change' => '0',
            'password_hash' => 'must-not-be-displayed',
        ]);

        $command = $this->getMockBuilder(InfoCommand::class)
            ->onlyMethods(['detectMagento', 'initMagento', 'detectCustomer'])->getMock();
        $command->method('detectMagento')->willReturn(true);
        $command->method('initMagento')->willReturn(true);
        $command->method('detectCustomer')->willReturn($customer);
        $application->add($command);
        $tester = new CommandTester($command);

        $this->assertSame(0, $tester->execute(['email' => 'user@example.com'], ['interactive' => false]));
        $firstDisplay = $tester->getDisplay();
        $this->assertStringContainsString('disable_auto_group_change', $firstDisplay);
        $this->assertStringNotContainsString('must-not-be-displayed', $firstDisplay);
        $this->assertSame(0, $tester->execute(['email' => 'user@example.com'], ['interactive' => false]));
        $this->assertSame($firstDisplay, $tester->getDisplay());
    }
}
