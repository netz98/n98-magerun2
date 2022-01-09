<?php

namespace N98\Magento\Command\Developer\Translate;

use Magento\Store\Model\ScopeInterface;
use Magento\Translation\Model\ResourceModel\StringUtils;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Config\Store\ConfigReaderTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetCommand extends AbstractMagentoCommand
{
    use ConfigReaderTrait;

    protected function configure()
    {
        $this
            ->setName('dev:translate:set')
            ->addArgument('string', InputArgument::REQUIRED, 'String to translate')
            ->addArgument('translate', InputArgument::REQUIRED, 'Translated string')
            ->addArgument('store', InputArgument::OPTIONAL)
            ->setDescription('Adds a translation to core_translate table. <comment>Globally for locale</comment>')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return;
        }

        $store = $this->getHelper('parameter')->askStore($input, $output);

        $localeCode = $this->getScopeConfigValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        $stringUtils = $this->getObjectManager()->get(StringUtils::class);
        $stringUtils->saveTranslate(
            $input->getArgument('string'),
            $input->getArgument('translate'),
            $localeCode,
            $store->getId()
        );

        $output->writeln(
            sprintf(
                'Translated (<info>%s</info>): <comment>%s</comment> => <comment>%s</comment>',
                $localeCode,
                $input->getArgument('string'),
                $input->getArgument('translate')
            )
        );
    }
}
