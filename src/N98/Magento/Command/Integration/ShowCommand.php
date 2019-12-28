<?php

namespace N98\Magento\Command\Integration;

use Magento\Integration\Model\IntegrationService;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Integration\Renderer\TableRenderer;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShowCommand
 * @package N98\Magento\Command\Integration
 */
class ShowCommand extends AbstractMagentoCommand
{
    /**
     * @var IntegrationService
     */
    private $integrationService;

    /**
     * @var OauthService
     */
    private $oauthService;

    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    protected function configure()
    {
        $this
            ->setName('integration:show')
            ->addArgument('name', InputArgument::REQUIRED, 'Name or ID of the integration')
            ->setDescription('Show details of an existing integration.');
    }

    public function inject(
        IntegrationService $integrationService,
        OauthService $oauthService,
        TokenCollectionFactory $tokenCollectionFactory
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
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

        $tokenModel = $this->tokenCollectionFactory
            ->create()
            ->addFilterByConsumerId($integrationModel->getConsumerId())
            ->getFirstItem();

        $table = new TableRenderer(
            $output,
            $integrationModel,
            $consumerModel,
            $tokenModel
        );
        $table->render();
    }
}
