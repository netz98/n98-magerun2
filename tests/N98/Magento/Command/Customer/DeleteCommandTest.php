<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\TestCase;

class DeleteCommandTest extends TestCase
{
    public function testDeleteByEmail(): void
    {
        $email = uniqid('', true) . '@example.com';

        $this->assertDisplayContains([
            'command'   => 'customer:create',
            'email'     => $email,
            'password'  => 'Password123',
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'website'   => $this->getWebsiteCode(),
        ], 'successfully created');

        $this->assertDisplayContains([
            'command' => 'customer:delete',
            '--email' => $email,
            '--force' => true,
        ], 'Successfully deleted 1 customer/s');
    }

    private function getWebsiteCode(): string
    {
        $storeManager = $this->getApplication()->getObjectManager()->get(StoreManagerInterface::class);

        return $storeManager->getWebsite('base')->getCode();
    }
}
