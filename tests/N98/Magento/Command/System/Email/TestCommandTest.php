<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Email;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class TestCommandTest extends TestCase
{
    public function testSendsUsingMockedMailTransport()
    {
        $transport = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['sendMessage'])
            ->getMock();
        $transport->expects($this->once())->method('sendMessage');

        $transportBuilder = $this->createMock(TransportBuilder::class);
        $transportBuilder->expects($this->once())->method('setTemplateIdentifier')
            ->with('contact_email_email_template')->willReturnSelf();
        $transportBuilder->expects($this->once())->method('setTemplateOptions')
            ->with(['area' => 'frontend', 'store' => 1])->willReturnSelf();
        $transportBuilder->expects($this->once())->method('setTemplateVars')->willReturnSelf();
        $transportBuilder->expects($this->once())->method('setFromByScope')
            ->with(['email' => 'sender@example.com', 'name' => 'Store Sender'], 1)->willReturnSelf();
        $transportBuilder->expects($this->once())->method('addTo')
            ->with('recipient@example.com')->willReturnSelf();
        $transportBuilder->expects($this->once())->method('setReplyTo')
            ->with('sender@example.com', 'Store Sender')->willReturnSelf();
        $transportBuilder->expects($this->once())->method('addCc')
            ->with('copy@example.com')->willReturnSelf();
        $transportBuilder->expects($this->once())->method('getTransport')->willReturn($transport);

        $inlineTranslation = $this->createMock(StateInterface::class);
        $inlineTranslation->expects($this->once())->method('suspend');
        $inlineTranslation->expects($this->once())->method('resume');

        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getCode')->willReturn('default');
        $store->method('getName')->willReturn('Default Store');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->once())->method('getStore')->with(null)->willReturn($store);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap([
            ['trans_email/ident_general/email', 'stores', 'default', 'sender@example.com'],
            ['trans_email/ident_general/name', 'stores', 'default', 'Store Sender'],
            ['contact/email/email_template', 'stores', 'default', 'contact_email_email_template'],
        ]);

        $command = new class() extends TestCommand {
            public function detectMagento(OutputInterface $output, $silent = true): bool
            {
                return true;
            }

            protected function initMagento(): bool
            {
                return true;
            }
        };
        $command->inject($transportBuilder, $storeManager, $scopeConfig, $inlineTranslation);

        $tester = new CommandTester($command);
        $status = $tester->execute([
            '--to' => 'recipient@example.com',
            '--cc' => ['copy@example.com'],
        ]);

        $this->assertSame(0, $status);
        $this->assertStringContainsString('Test email sent to', $tester->getDisplay());
    }

    public function testMissingToOptionFailsWithoutPromptingOrSendingMail()
    {
        // Run with interactive=false so the "to" prompt resolves to its empty default instead
        // of reading from stdin. This guarantees the command fails validation before it ever
        // reaches the mail transport, regardless of the test runner's stdin/TTY state.
        $tester = new CommandTester($this->getApplication()->find('sys:email:test'));
        $status = $tester->execute([], ['interactive' => false]);

        $this->assertSame(1, $status);
        $this->assertStringContainsString(
            'Please provide a valid recipient email address with --to',
            $tester->getDisplay()
        );
    }

    public function testInvalidToOptionFails()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:email:test', '--to' => 'not-an-email'],
            'Please provide a valid recipient email address with --to'
        );
    }

    public function testInvalidFromOptionFails()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:email:test', '--to' => 'test@example.com', '--from' => 'not-an-email'],
            'The --from email address is not valid'
        );
    }

    public function testInvalidCcOptionFails()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:email:test', '--to' => 'test@example.com', '--cc' => ['not-an-email']],
            'not-an-email" is not valid'
        );
    }
}
