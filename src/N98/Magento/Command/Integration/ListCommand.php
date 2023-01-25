<?php

namespace N98\Magento\Command\Integration;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\ResourceModel\Integration\Collection;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Integration
 */
class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Integration\Model\IntegrationFactory
     */
    private $integrationFactory;

    /**
     * @var \Magento\Integration\Model\OauthService
     */
    private $oauthService;

    /**
     * @var \Magento\Integration\Model\AuthorizationService
     */
    private $authorizationService;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    private $tokenFactory;

    protected function configure()
    {
        $this
            ->setName('integration:list')
            ->setDescription('List all existing integrations.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    public function inject(
        \Magento\Integration\Model\IntegrationFactory $integrationFactory,
        \Magento\Integration\Model\OauthService $oauthService,
        \Magento\Integration\Model\AuthorizationService $authorizationService,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
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
        $integrationModel = $this->integrationFactory->create();

        /** @var Collection $collection */
        $collection = $integrationModel->getCollection();

        $integrations = $collection->getItems();

        /** @var Integration $integration */
        $table = [];
        foreach ($integrations as $integration) {
            switch ($integration->getStatus()) {
                case Integration::STATUS_ACTIVE:
                    break;
            }

            $table[] = [
                $integration->getId(),
                $integration->getName(),
                $integration->getEmail(),
                $integration->getEndpoint(),
                // return type is not int as defined in Magento. Do not check type strict here.
                $integration->getSetupType() == Integration::TYPE_MANUAL ? 'Manual' : 'Config',
                Integration::STATUS_ACTIVE ? 'Active' : 'Inactive',
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['id', 'name', 'email', 'endpoint', 'type', 'status'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
