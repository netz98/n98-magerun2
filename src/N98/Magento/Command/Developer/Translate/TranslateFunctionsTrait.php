<?php

namespace N98\Magento\Command\Developer\Translate;

use Magento\Store\Api\Data\StoreInterface;

trait TranslateFunctionsTrait
{
    /**
     * Determine if a developer restriction is in place, and if we're enabling something that will use it
     * then notify and ask if it needs to be changed from its current value.
     *
     * @param \Magento\Store\Api\Data\StoreInterface  $store
     * @param  bool $enabled
     * @return void
     */
    protected function detectAskAndSetDeveloperIp(StoreInterface $store, bool $enabled)
    {
        if (!$enabled) {
            // No need to notify about developer IP restrictions if we're disabling template hints etc
            return;
        }

        /** @var OutputInterface $output */
        $output = $this->getHelper('io')->getOutput();

        if (!$devRestriction = $store->getConfig('dev/restrict/allow_ips')) {
            return;
        }

        $this->askAndSetDeveloperIp($output, $store, $devRestriction);
    }
}