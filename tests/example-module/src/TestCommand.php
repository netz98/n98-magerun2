<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98MagerunExampleModule;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    protected function configure()
    {
        $this
            ->setName('magerun:example-modulet:test')
            ->setDescription('Test command for functional testing')
        ;
    }

    /**
     * Inject some stuff of Magento to test injection
     *
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @return void
     */
    public function inject(\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Execute test command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $output->writeln($this->priceCurrency->format(98, false));
        }
    }
}
