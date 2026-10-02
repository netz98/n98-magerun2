<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;
use N98\Util\Console\Helper\ParameterHelper;
use Symfony\Component\Console\Tester\CommandTester;

class AddAddressCommandTest extends TestCase
{
    public function testRepeatedAddressCreationWithAnExistingArea()
    {
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $state = $objectManager->get(State::class);
        $website = $objectManager->get(StoreManagerInterface::class)->getWebsite('base');
        $customer = $this->createMock(CustomerInterface::class);
        $repository = $this->createMock(CustomerRepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('get')
            ->with('user@example.com', $website->getId())->willReturn($customer);

        $command = $this->getMockBuilder(AddAddressCommand::class)
            ->onlyMethods(['detectMagento', 'initMagento', 'createAddress', 'injectObjects'])->getMock();
        $command->method('detectMagento')->willReturn(true);
        $command->method('initMagento')->willReturn(true);
        $command->expects($this->exactly(2))->method('createAddress')->willReturnCallback(function () use ($state) {
            $this->assertSame('frontend', $state->getAreaCode());

            return false;
        });
        $command->inject($repository, $state);
        $application->add($command);
        $parameterHelper = $this->createMock(ParameterHelper::class);
        $parameterHelper->method('getName')->willReturn('parameter');
        $parameterHelper->method('askEmail')->willReturn('user@example.com');
        $parameterHelper->method('askWebsite')->willReturn($website);
        $command->getHelperSet()->set($parameterHelper, 'parameter');
        $tester = new CommandTester($command);

        $state->emulateAreaCode('adminhtml', function () use ($tester, $state) {
            for ($run = 0; $run < 2; $run++) {
                $status = $tester->execute([
                    'email' => 'user@example.com',
                    '--firstname' => 'John',
                    '--lastname' => 'Doe',
                    '--street' => '123 Main St',
                    '--city' => 'Berlin',
                    '--country' => 'DE',
                    '--postcode' => '10115',
                    '--telephone' => '123456789',
                ], ['interactive' => false]);

                $this->assertSame(0, $status, $tester->getDisplay());
                $this->assertSame('adminhtml', $state->getAreaCode());
            }
        });
    }
}
