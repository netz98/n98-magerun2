<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;
use N98\Magento\Mcp\CommandToolHandler;
use Symfony\Component\Console\Tester\CommandTester;

class ChangePasswordCommandTest extends TestCase
{
    /**
     * @dataProvider invalidPasswordProvider
     */
    public function testInvalidPasswordLeavesStoredHashUnchanged(array $arguments, string $message, bool $interactive)
    {
        if ($interactive && stream_isatty(STDIN)) {
            $this->markTestSkipped('This case requires stdin without a TTY.');
        }
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $website = $objectManager->get(StoreManagerInterface::class)->getWebsite('base');
        $customer = $objectManager->create(Customer::class);
        $customer->setWebsiteId($website->getId())
            ->setEmail(uniqid('password-test-', true) . '@example.com')
            ->setFirstname('Password')->setLastname('Test')
            ->setPasswordHash($objectManager->get(EncryptorInterface::class)->getHash('Known-Passw0rd!', true));
        $resource = $customer->getResource();
        $objectManager->get(Registry::class)->register('isSecureArea', true);
        $resource->save($customer);
        $originalHash = $customer->getPasswordHash();

        try {
            $tester = new CommandTester($application->find('customer:change-password'));
            $status = $tester->execute($arguments + [
                'email' => $customer->getEmail(),
                'website' => $website->getCode(),
            ], ['interactive' => $interactive]);

            $this->assertSame(1, $status);
            $this->assertStringContainsString($message, $tester->getDisplay());
            $this->assertStringNotContainsString('Password successfully changed', $tester->getDisplay());
            $resource->load($customer, $customer->getId());
            $this->assertSame($originalHash, $customer->getPasswordHash());
        } finally {
            $resource->delete($customer);
            $objectManager->get(Registry::class)->unregister('isSecureArea');
        }
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            'missing' => [[], 'A password is required in non-interactive mode', false],
            'missing without a TTY' => [[], 'A password is required in non-interactive mode', true],
            'empty' => [['password' => ''], 'The password must not be empty', false],
            'empty interactive argument' => [['password' => ''], 'The password must not be empty', true],
        ];
    }

    /**
     * @dataProvider executionModeProvider
     */
    public function testRepeatedPasswordChangesWithAnExistingArea(bool $viaMcp)
    {
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $website = $objectManager->get(StoreManagerInterface::class)->getWebsite('base');
        $customer = $objectManager->create(Customer::class);
        $customer->setWebsiteId($website->getId())
            ->setEmail(uniqid('password-test-', true) . '@example.com')
            ->setFirstname('Password')->setLastname('Test');
        $resource = $customer->getResource();
        $objectManager->get(Registry::class)->register('isSecureArea', true);
        $resource->save($customer);
        $state = $objectManager->get(State::class);
        $encryptor = $objectManager->get(EncryptorInterface::class);
        $tester = new CommandTester($application->find('customer:change-password'));
        $handler = new CommandToolHandler($application, 'customer:change-password');

        try {
            $state->emulateAreaCode('adminhtml', function () use ($tester, $handler, $viaMcp, $customer, $website, $resource, $state, $encryptor) {
                foreach (['First-Passw0rd!', 'Second Passw0rd!'] as $password) {
                    if ($viaMcp) {
                        $display = $handler(sprintf('%s "%s" %s', $customer->getEmail(), $password, $website->getCode()));
                    } else {
                        $status = $tester->execute([
                            'email' => $customer->getEmail(),
                            'password' => $password,
                            'website' => $website->getCode(),
                        ], ['interactive' => false]);
                        $display = $tester->getDisplay();
                        $this->assertSame(0, $status, $display);
                    }

                    $this->assertStringContainsString('Password successfully changed', $display);
                    $this->assertSame('adminhtml', $state->getAreaCode());
                    $resource->load($customer, $customer->getId());
                    $this->assertTrue($encryptor->validateHash($password, $customer->getPasswordHash()));
                }
            });
        } finally {
            $resource->delete($customer);
            $objectManager->get(Registry::class)->unregister('isSecureArea');
        }
    }

    public static function executionModeProvider(): array
    {
        return ['CLI' => [false], 'MCP' => [true]];
    }
}
