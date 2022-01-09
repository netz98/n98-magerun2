<?php

namespace N98\Magento\Command\Developer\Translate;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use N98\Magento\Command\Config\Store\ConfigReaderTrait;
use N98\Magento\Command\Config\Store\ConfigWriterTrait;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait TranslateFunctionsTrait
{
    use ConfigReaderTrait;
    use ConfigWriterTrait;

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

        $input = new ArgvInput();
        $output = new ConsoleOutput();

        $devRestriction = $this->getScopeConfigValue(
            'dev/restrict/allow_ips',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if (!$devRestriction) {
            return;
        }

        $this->askAndSetDeveloperIp($input, $output, $store, $devRestriction);
    }

    /**
     * Ask if the developer IP should be changed, and change it if required
     *
     * @param  OutputInterface $output
     * @param  StoreInterface $store
     * @param  string|null $devRestriction
     * @return void
     */
    protected function askAndSetDeveloperIp(
        InputInterface $input,
        OutputInterface $output,
        StoreInterface $store,
        $devRestriction
    ) {
        $output->writeln(
            sprintf(
                '<comment><info>Please note:</info> developer IP restriction is enabled for <info>%s</info>.',
                $devRestriction
            )
        );

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');
        $newDeveloperIp = $questionHelper->ask(
            $input,
            $output,
            new Question(
                '<question>Change developer IP? Enter a new IP to change or leave blank to skip.</question>: '
            )
        );

        if (empty($newDeveloperIp)) {
            return;
        }

        $this->setDeveloperIp($store, $newDeveloperIp);
        $output->writeln(sprintf('<comment><info>New developer IP restriction set to %s', $newDeveloperIp));
    }

    /**
     * Set the restricted IP for developer access
     *
     * @param StoreInterface $store
     * @param string $newDeveloperIp
     */
    protected function setDeveloperIp(StoreInterface $store, $newDeveloperIp)
    {
        $this->saveScopeConfigValue('dev/restrict/allow_ips', $newDeveloperIp, 'stores', $store->getId());
    }
}
