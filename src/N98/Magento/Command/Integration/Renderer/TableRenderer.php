<?php

namespace N98\Magento\Command\Integration\Renderer;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Integration\Model\Oauth\Token;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TableRenderer
 * @package N98\Magento\Command\Integration\Renderer
 */
class TableRenderer
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Integration
     */
    private $integrationModel;

    /**
     * @var Consumer
     */
    private $consumerModel;

    /**
     * @var Token
     */
    private $tokenModel;

    /**
     * TableRenderer constructor.
     * @param OutputInterface $output
     * @param Integration $integrationModel
     * @param Consumer $consumerModel
     * @param Token $tokenModel
     */
    public function __construct(
        OutputInterface $output,
        Integration $integrationModel,
        Consumer $consumerModel,
        Token $tokenModel
    ) {
        $this->output = $output;
        $this->integrationModel = $integrationModel;
        $this->consumerModel = $consumerModel;
        $this->tokenModel = $tokenModel;
    }

    public function render()
    {
        $table = new Table($this->output);
        $table->setRows([
            ['Integration ID', $this->integrationModel->getId()],
            ['Name', $this->integrationModel->getName()],
            ['Email', $this->integrationModel->getEmail()],
            ['Endpoint', $this->integrationModel->getEndpoint()],
            ['Status', $this->integrationModel->getStatus() == Integration::STATUS_ACTIVE ? 'Active' : 'Inactive'],
            new TableSeparator(),
            ['Consumer Key', $this->consumerModel->getKey()],
            ['Consumer Secret', $this->consumerModel->getSecret()],
            new TableSeparator(),
            ['Access Token', $this->tokenModel->getToken()],
            ['Access Token Secret', $this->tokenModel->getSecret()],
        ]);
        $table->render();
    }
}
