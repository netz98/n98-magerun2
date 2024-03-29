<?php

namespace N98\Magento\Command\Developer;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DecryptCommand
 * @package N98\Magento\Command\Developer
 */
class decryptCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EavConfig $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @return void
     */
    public function inject(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dev:decrypt')
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Value you wish to decrypt'
            )
            ->setDescription('Decrypt the given value using magento\'s crypt key');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $value = $input->getArgument('value');

        $output->writeln($this->encryptor->decrypt($value));

        return Command::SUCCESS;
    }
}
