<?php

namespace N98\Magento\Command\Customer;

use Exception;
use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Customer\Model\Resource\Customer;
use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends AbstractCustomerCommand
{
    /**
     * @var array
     */
    protected $blacklist = [
        'password_hash',
        'increment_id',
    ];

    protected function configure()
    {
        $this
            ->setName('customer:info')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website of the customer')
            ->setDescription('Loads basic customer info by email address.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Detect and Init Magento
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        // Detect customer
        $customer = $this->detectCustomer($input, $output);
        if ($customer === null) {
            return;
        }

        /** @var Customer $customerResource */
        $customerResource = $customer->getResource();

        // Prepare Table Data
        $table = [];
        foreach ($customer->toArray() as $key => $value) {
            if (in_array($key, $this->blacklist)) {
                continue;
            }
            try {
                $attribute = $customerResource->getAttribute($key);

                if (!$attribute instanceof CustomerAttribute) {
                    $table[] = [$key, $key, $value];
                    continue;
                }

                /** @var DefaultFrontend $attributeFrontend */
                $attributeFrontend = $attribute->getFrontend();

                $tableLabel = $attributeFrontend->getLabel();
                $tableValue = $value;

                // @todo mwr temporary work around due to getValue throwing notice within getOptionText
                // (array keys !== store_ids)
                if ($key != 'store_id') {
                    $tableValue = $attributeFrontend->getValue($customer);
                    if ($tableValue != $value) {
                        $tableValue .= " [$value]";
                    }
                }
                $table[] = [$key, $tableLabel, $tableValue];
            } catch (Exception $e) {
                $table[] = [$key, $key, $value];
            }
        }

        // Render Table
        $helperTable = $this->getHelper('table');
        $helperTable->setHeaders(['Code', 'Name', 'Value']);
        $helperTable->setRows($table);
        $helperTable->render($output);
    }
}
