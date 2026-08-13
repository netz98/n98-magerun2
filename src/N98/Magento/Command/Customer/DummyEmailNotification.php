<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotificationInterface;

class DummyEmailNotification implements EmailNotificationInterface
{
    public function credentialsChanged(
        CustomerInterface $savedCustomer,
        $origCustomerEmail,
        $isPasswordChanged = false
    ) {
        // Do nothing to prevent emails
    }

    public function passwordReminder(CustomerInterface $customer)
    {
        // Do nothing to prevent emails
    }

    public function passwordResetConfirmation(CustomerInterface $customer)
    {
        // Do nothing to prevent emails
    }

    public function newAccount(
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        // Do nothing to prevent emails
    }
}
