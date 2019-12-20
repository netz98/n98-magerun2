<?php

namespace N98\Magento\Command\Customer;

use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateCommandTest
 * @package N98\Magento\Command\Customer
 */
class CreateCommandTest extends TestCase
{
    public function testExecute()
    {
        $generatedEmail = uniqid() . '@example.com';

        $input = [
            'command'   => 'customer:create',
            'email'     => $generatedEmail,
            'password'  => 'Password123',
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'website'   => $this->getWebsiteCode(),
        ];
        $this->assertDisplayContains($input, 'successfully created');

        // Format option
        $generatedEmail = uniqid() . '@example.com';
        $input['email'] = $generatedEmail;
        $input['--format'] = 'csv';

        $this->assertDisplayContains($input, 'email,password,firstname,lastname');
        $this->assertdisplayContains($input, $generatedEmail . ',Password123,John,Doe');
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

    public function testWithWrongPassword()
    {
        $this->markTestIncomplete('We currently cannot deal with interactive commands');

        $application = $this->getApplication();
        $application->add(new CreateCommand());

        // try to create a customer with a password < 6 chars
        $command = $this->getApplication()->find('customer:create');

        $generatedEmail = uniqid() . '@example.com';

        // mock dialog
        // We mock the DialogHelper
        $dialog = $this->createMock('N98\Util\Console\Helper\ParameterHelper', ['askPassword']);
        $dialog->expects($this->at(0))
            ->method('askPassword')
            ->will($this->returnValue(true)); // The user confirms

        // We override the standard helper with our mock
        $command->getHelperSet()->set($dialog, 'parameter');

        $options = [
            'command'   => $command->getName(),
            'email'     => $generatedEmail,
            'password'  => 'pass',
            'firstname' => 'John',
            'lastname'  => 'Doe',
        ];
        $commandTester = new CommandTester($command);
        $commandTester->execute($options);
        $this->assertRegExp(
            '/The password must have at least 6 characters. Leading or trailing spaces will be ignored./',
            $commandTester->getDisplay()
        );
    }
}
