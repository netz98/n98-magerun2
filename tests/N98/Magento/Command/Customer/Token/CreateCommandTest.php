<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer\Token;

use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateCommandTest
 * @package N98\Magento\Command\Customer\Token
 */
class CreateCommandTest extends TestCase
{
    public function testExecute()
    {
        $generatedEmail = uniqid() . '@example.com';

        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(
            new ArrayInput(
                [
                    'command'   => 'customer:create',
                    'email'     => $generatedEmail,
                    'password'  => 'Password123',
                    'firstname' => 'John',
                    'lastname'  => 'Doe',
                    'website'   => $this->getWebsiteCode(),
                ]
            ),
            new NullOutput()
        );

        $command = $this->getApplication()->find('customer:token:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'email'        => $generatedEmail,
            '--no-newline' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertNotEmpty($output);
        $this->greaterThanOrEqual(32, strlen($output));
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
