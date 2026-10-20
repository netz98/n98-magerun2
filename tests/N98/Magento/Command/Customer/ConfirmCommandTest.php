<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConfirmCommandTest extends TestCase
{
    /**
     * @dataProvider invalidEmailProvider
     */
    public function testInvalidNonInteractiveEmailDoesNotCallCustomerServices(string $email, bool $resend)
    {
        $application = $this->getApplication();
        $accountManagement = $this->createMock(AccountManagementInterface::class);
        $accountManagement->expects($this->never())->method($this->anything());
        $repository = $this->createMock(CustomerRepositoryInterface::class);
        $repository->expects($this->never())->method($this->anything());
        $command = new class($accountManagement, $repository) extends ConfirmCommand {
            private AccountManagementInterface $testAccountManagement;
            private CustomerRepositoryInterface $testRepository;

            public function __construct(AccountManagementInterface $accountManagement, CustomerRepositoryInterface $repository)
            {
                $this->testAccountManagement = $accountManagement;
                $this->testRepository = $repository;
                parent::__construct();
            }

            public function inject(
                AccountManagementInterface $accountManagement,
                CustomerRepositoryInterface $customerRepository,
                StoreManagerInterface $storeManager,
                State $state,
                Emulation $emulation
            ) {
                parent::inject($this->testAccountManagement, $this->testRepository, $storeManager, $state, $emulation);
            }
        };
        $application->add($command);
        $tester = new CommandTester($command);
        $this->assertSame(1, $tester->execute([
            'email' => $email, 'website' => 'base', '--resend' => $resend,
        ], ['interactive' => false]));
        $this->assertStringContainsString('A valid email address is required', $tester->getDisplay());
        $this->assertStringNotContainsString('Email:', $tester->getDisplay());
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'malformed confirm' => ['malformed-email', false],
            'malformed resend' => ['malformed-email', true],
            'whitespace confirm' => ['   ', false],
            'whitespace resend' => ["\t\n", true],
        ];
    }

    /**
     * @dataProvider resendFailureProvider
     */
    public function testResendFailurePreservesAccountAndContext(bool $throws)
    {
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $website = $objectManager->get(StoreManagerInterface::class)->getWebsite('base');
        $customer = $objectManager->create(Customer::class);
        $customer->setWebsiteId($website->getId())->setStoreId($website->getDefaultStore()->getId())
            ->setEmail(uniqid('resend-failure-', true) . '@example.com')
            ->setFirstname('Resend')->setLastname('Failure')->setConfirmation('pending-failure-key');
        $resource = $customer->getResource();
        $registry = $objectManager->get(Registry::class);
        $registry->register('isSecureArea', true);
        $resource->save($customer);
        $accountManagement = $this->createMock(AccountManagementInterface::class);
        $expectation = $accountManagement->expects($this->once())->method('resendConfirmation')
            ->with($customer->getEmail(), $website->getId());
        if ($throws) {
            $expectation->willThrowException(new \RuntimeException('Email service unavailable'));
        } else {
            $expectation->willReturn(false);
        }
        $accountManagement->expects($this->never())->method('activateById');
        $command = new class($accountManagement) extends ConfirmCommand {
            private AccountManagementInterface $testAccountManagement;

            public function __construct(AccountManagementInterface $accountManagement)
            {
                $this->testAccountManagement = $accountManagement;
                parent::__construct();
            }

            public function inject(
                AccountManagementInterface $accountManagement,
                CustomerRepositoryInterface $customerRepository,
                StoreManagerInterface $storeManager,
                State $state,
                Emulation $emulation
            ) {
                parent::inject($this->testAccountManagement, $customerRepository, $storeManager, $state, $emulation);
            }
        };
        $application->add($command);
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $originalStoreId = $storeManager->getStore()->getId();
        try {
            $tester = new CommandTester($command);
            $state = $objectManager->get(State::class);
            $state->emulateAreaCode('adminhtml', function () use ($tester, $customer, $website, $state) {
                $this->assertSame(1, $tester->execute([
                    'email' => $customer->getEmail(), 'website' => $website->getCode(), '--resend' => true,
                ], ['interactive' => false]));
                $this->assertStringNotContainsString('successfully', $tester->getDisplay());
                $this->assertSame('adminhtml', $state->getAreaCode());
            });
            $this->assertSame($originalStoreId, $storeManager->getStore()->getId());
            $resource->load($customer, $customer->getId());
            $this->assertSame('pending-failure-key', $customer->getConfirmation());
        } finally {
            $resource->delete($customer);
            $registry->unregister('isSecureArea');
        }
    }

    public static function resendFailureProvider(): array
    {
        return ['returns false' => [false], 'throws' => [true]];
    }

    /**
     * @dataProvider accountStateProvider
     */
    public function testAccountState(bool $pending, bool $resend, int $status, string $message)
    {
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $website = $objectManager->get(StoreManagerInterface::class)->getWebsite('base');
        $customer = $objectManager->create(Customer::class);
        $customer->setWebsiteId($website->getId())->setStoreId($website->getDefaultStore()->getId())
            ->setEmail(uniqid('confirm-', true) . '@example.com')
            ->setFirstname('Confirm')->setLastname('Test')
            ->setConfirmation($pending ? 'pending-test-key' : null);
        $resource = $customer->getResource();
        $registry = $objectManager->get(Registry::class);
        $registry->register('isSecureArea', true);
        $resource->save($customer);
        $originalStoreId = $objectManager->get(StoreManagerInterface::class)->getStore()->getId();

        try {
            $tester = new CommandTester($application->find('customer:confirm'));
            $state = $objectManager->get(State::class);
            $state->emulateAreaCode('adminhtml', function () use ($tester, $customer, $website, $resend, $status, $message, $state) {
                $this->assertSame($status, $tester->execute([
                    'email' => $customer->getEmail(),
                    'website' => $resend ? $website->getCode() : null,
                    '--resend' => $resend,
                ], ['interactive' => false]), $tester->getDisplay());
                $this->assertStringContainsString($message, $tester->getDisplay());
                $this->assertSame('adminhtml', $state->getAreaCode());
            });
            $resource->load($customer, $customer->getId());
            if ($pending && $resend) {
                $this->assertSame('pending-test-key', $customer->getConfirmation());
            } else {
                $this->assertEmpty($customer->getConfirmation());
            }
            $this->assertSame($originalStoreId, $objectManager->get(StoreManagerInterface::class)->getStore()->getId());
        } finally {
            $resource->delete($customer);
            $registry->unregister('isSecureArea');
        }
    }

    public static function accountStateProvider(): array
    {
        return [
            'confirm pending' => [true, false, 0, 'Customer account successfully confirmed'],
            'resend pending' => [true, true, 0, 'Confirmation email successfully sent. Account remains pending'],
            'confirmed default' => [false, false, 1, 'already confirmed'],
            'confirmed resend' => [false, true, 1, 'already confirmed'],
        ];
    }

    /**
     * @dataProvider invalidSelectionProvider
     */
    public function testInvalidSelection(array $arguments, string $message)
    {
        $tester = new CommandTester($this->getApplication()->find('customer:confirm'));
        $this->assertSame(1, $tester->execute($arguments, ['interactive' => false]));
        $this->assertStringContainsString($message, $tester->getDisplay());
    }

    public static function invalidSelectionProvider(): array
    {
        return [
            'missing email' => [[], 'An email is required'],
            'missing customer' => [['email' => 'missing-confirm@example.invalid', 'website' => 'base'], 'was not found'],
            'invalid website' => [['email' => 'missing-confirm@example.invalid', 'website' => 'no-such-confirm-website'], 'was not found'],
        ];
    }

    public function testWrongWebsiteDoesNotConfirmCustomer()
    {
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $website = $objectManager->create(Website::class);
        $website->setCode('confirm_test')->setName('Confirm test website');
        $website->getResource()->save($website);
        $objectManager->get(StoreManagerInterface::class)->reinitStores();
        $customer = $objectManager->create(Customer::class);
        $customer->setWebsiteId($website->getId())->setEmail(uniqid('scoped-confirm-', true) . '@example.com')
            ->setFirstname('Scope')->setLastname('Test')->setConfirmation('pending-scoped-key');
        $resource = $customer->getResource();
        $registry = $objectManager->get(Registry::class);
        $registry->register('isSecureArea', true);
        $resource->save($customer);
        try {
            $tester = new CommandTester($application->find('customer:confirm'));
            $this->assertSame(1, $tester->execute([
                'email' => $customer->getEmail(),
            ], ['interactive' => false]));
            $this->assertStringContainsString('A website is required', $tester->getDisplay());
            foreach ([false, true] as $resend) {
                $tester = new CommandTester($application->find('customer:confirm'));
                $this->assertSame(1, $tester->execute([
                    'email' => $customer->getEmail(), 'website' => 'base', '--resend' => $resend,
                ], ['interactive' => false]));
                $this->assertStringContainsString('was not found', $tester->getDisplay());
                $resource->load($customer, $customer->getId());
                $this->assertSame('pending-scoped-key', $customer->getConfirmation());
            }
        } finally {
            $resource->delete($customer);
            $website->getResource()->delete($website);
            $registry->unregister('isSecureArea');
        }
    }

    public function testSameEmailOnTwoWebsitesOnlyConfirmsSelectedAccount()
    {
        $application = $this->getApplication();
        $objectManager = $application->getObjectManager();
        $base = $objectManager->get(StoreManagerInterface::class)->getWebsite('base');
        $website = $objectManager->create(Website::class);
        $website->setCode('confirm_same_email')->setName('Confirm same email website');
        $website->getResource()->save($website);
        $registry = $objectManager->get(Registry::class);
        $registry->register('isSecureArea', true);
        $customers = [];
        $email = uniqid('same-email-confirm-', true) . '@example.com';
        try {
            foreach ([$base, $website] as $site) {
                $customer = $objectManager->create(Customer::class);
                $customer->setWebsiteId($site->getId())->setStoreId($base->getDefaultStore()->getId())
                    ->setEmail($email)->setFirstname('Same')->setLastname('Email')
                    ->setConfirmation('pending-same-email-key');
                $customer->getResource()->save($customer);
                $customers[] = $customer;
            }
            $tester = new CommandTester($application->find('customer:confirm'));
            // Website IDs, as well as codes, must be accepted.
            $this->assertSame(0, $tester->execute([
                'email' => $email, 'website' => $base->getId(), '--resend' => true,
            ], ['interactive' => false]), $tester->getDisplay());
            foreach ($customers as $customer) {
                $customer->getResource()->load($customer, $customer->getId());
                $this->assertSame('pending-same-email-key', $customer->getConfirmation());
            }
            $this->assertSame(0, $tester->execute([
                'email' => $email, 'website' => $base->getId(),
            ], ['interactive' => false]), $tester->getDisplay());
            $customers[0]->getResource()->load($customers[0], $customers[0]->getId());
            $customers[1]->getResource()->load($customers[1], $customers[1]->getId());
            $this->assertEmpty($customers[0]->getConfirmation());
            $this->assertSame('pending-same-email-key', $customers[1]->getConfirmation());
        } finally {
            foreach ($customers as $customer) {
                $customer->getResource()->delete($customer);
            }
            $website->getResource()->delete($website);
            $registry->unregister('isSecureArea');
        }
    }
}
