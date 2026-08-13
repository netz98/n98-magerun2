<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateDummyCommandTest
 * @package N98\Magento\Command\Customer
 */
class CreateDummyCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = [
            'command' => 'customer:create:dummy',
            'count'   => 2,
            'locale'  => 'de_DE',
            'website' => $this->getWebsiteCode(),
        ];

        // Ensure we output "successfully created"
        $this->assertDisplayContains($input, 'successfully created');

        // Test with format option
        $input2 = [
            'command'  => 'customer:create:dummy',
            'count'    => 1,
            'locale'   => 'en_US',
            'website'  => $this->getWebsiteCode(),
            '--format' => 'csv',
        ];
        $this->assertDisplayContains($input2, 'email,password,firstname,lastname');
    }

    public function testExecuteWithAddresses()
    {
        $input = [
            'command'          => 'customer:create:dummy',
            'count'            => 1,
            'locale'           => 'fr_FR',
            'website'          => $this->getWebsiteCode(),
            '--with-addresses' => true,
        ];

        $command = $this->getApplication()->find('customer:create:dummy');
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('successfully created', $display);

        // Find the created customer email from the console output using a regex
        preg_match('/Customer\s+(.+?)\s+successfully created/', strip_tags($display), $matches);
        $this->assertNotEmpty($matches, 'Could not find created customer email in display: ' . $display);

        $email = trim($matches[1]);

        // Load the customer from the database and verify they have an address
        $objectManager = $this->getApplication()->getObjectManager();
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->get(CustomerRepositoryInterface::class);

        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite($this->getWebsiteCode());

        $customer = $customerRepository->get($email, $website->getId());
        $this->assertNotNull($customer);
        $this->assertEquals($email, $customer->getEmail());

        $addresses = $customer->getAddresses();
        $this->assertCount(1, $addresses, 'Expected customer to have 1 address');

        $address = $addresses[0];
        $this->assertEquals('FR', $address->getCountryId(), 'Expected country ID to be FR based on fr_FR locale');
        $this->assertNotEmpty($address->getStreet());
        $this->assertNotEmpty($address->getCity());
        $this->assertNotEmpty($address->getPostcode());
    }

    public function testExecuteWithPrintPassword()
    {
        $input = [
            'command'          => 'customer:create:dummy',
            'count'            => 1,
            'locale'           => 'en_US',
            'website'          => $this->getWebsiteCode(),
            '--print-password' => true,
        ];

        $command = $this->getApplication()->find('customer:create:dummy');
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $display = $commandTester->getDisplay();

        $this->assertStringContainsString('successfully created with password', $display);

        preg_match('/with password\s+(\S+)/', strip_tags($display), $matches);
        $this->assertNotEmpty($matches, 'Could not find password in display: ' . $display);

        $password = trim($matches[1]);
        $this->assertNotEquals('Password123!', $password);
        $this->assertGreaterThanOrEqual(8, strlen($password));
    }

    /**
     * @return string
     */
    private function getWebsiteCode()
    {
        $storeManager = $this->getApplication()->getObjectManager()->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite('base');

        return $website->getCode();
    }
}
