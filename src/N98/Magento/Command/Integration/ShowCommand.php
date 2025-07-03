<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Integration;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\IntegrationService;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\OauthService;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShowCommand
 * @package N98\Magento\Command\Integration
 */
class ShowCommand extends AbstractMagentoCommand
{
    use IntegrationDataTrait;

    /**
     * @var IntegrationService
     */
    private $integrationService;

    /**
     * @var OauthService
     */
    private $oauthService;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var array
     */
    private $infos = [];

    protected function configure()
    {
        $this
            ->setName('integration:show')
            ->addArgument('name', InputArgument::REQUIRED, 'Name or ID of the integration')
            ->setDescription('Show details of an existing integration.')
            ->addArgument(
                'key',
                InputArgument::OPTIONAL,
                'Only output value of named param like "Access Token". Key is case insensitive.'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    public function inject(
        IntegrationService $integrationService,
        OauthService $oauthService,
        TokenFactory $tokenFactory
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $integrationName = $input->getArgument('name');

        $integrationModel = $this->integrationService->findByName($integrationName);

        if ($integrationModel->getId() <= 0 && is_numeric($integrationName)) {
            $integrationModel = $this->integrationService->get($integrationName);
        }

        if ($integrationModel->getId() <= 0) {
            throw new RuntimeException('Integration with this name or ID does not exist.');
        }

        $consumerModel = $this->oauthService->loadConsumer($integrationModel->getConsumerId());

        $tokenModel = $this->tokenFactory->create();
        $tokenModel->loadByConsumerIdAndUserType(
            $integrationModel->getConsumerId(),
            UserContextInterface::USER_TYPE_INTEGRATION
        );

        $data = array_merge(
            $this->getIntegrationData($integrationModel),
            $this->getConsumerData($consumerModel),
            $this->getTokenData($tokenModel)
        );

        if (($settingArgument = $input->getArgument('key')) !== null) {
            $settingArgument = strtolower($settingArgument);
            $data = array_change_key_case($data, CASE_LOWER);
            if (!isset($data[$settingArgument])) {
                throw new \InvalidArgumentException('Unknown key: ' . $settingArgument);
            }
            $output->writeln((string)$data[$settingArgument]);

            return Command::SUCCESS;
        }

        $table = [];

        foreach ($data as $key => $value) {
            $table[] = [$key, $value];
        }

        if (($settingArgument = $input->getArgument('key')) !== null) {
            $settingArgument = strtolower($settingArgument);
            $data = array_change_key_case($data, CASE_LOWER);
            if (!isset($data[$settingArgument])) {
                throw new \InvalidArgumentException('Unknown key: ' . $settingArgument);
            }
            $output->writeln((string)$this->infos[$settingArgument]);
        } else {
            $this->getHelper('table')
                ->setHeaders(['name', 'value'])
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return Command::SUCCESS;
    }
}
