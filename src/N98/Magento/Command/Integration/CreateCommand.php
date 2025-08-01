<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Integration;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Oauth\Exception;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\Integration as IntegrationAlias;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\IntegrationService;
use Magento\Integration\Model\Oauth\Consumer as ConsumerModel;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\ResourceModel\Oauth\Consumer;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 * @package N98\Magento\Command\Integration
 */
class CreateCommand extends AbstractMagentoCommand
{
    use IntegrationDataTrait;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    /**
     * @var OauthService
     */
    private $oauthService;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var IntegrationService
     */
    private $integrationService;

    /**
     * @var Consumer
     */
    private $consumerResource;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Token
     */
    private $tokenResource;

    protected function configure()
    {
        $this
            ->setName('integration:create')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the integration')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('endpoint', InputArgument::OPTIONAL, 'Endpoint URL')
            ->addOption('consumer-key', '', InputOption::VALUE_REQUIRED, 'Consumer Key (length 32 chars)')
            ->addOption('consumer-secret', '', InputOption::VALUE_REQUIRED, 'Consumer Secret (length 32 chars)')
            ->addOption('access-token', '', InputOption::VALUE_REQUIRED, 'Access-Token (length 32 chars)')
            ->addOption('access-token-secret', '', InputOption::VALUE_REQUIRED, 'Access-Token Secret (length 32 chars)')
            ->addOption('resource', 'r', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Defines a granted ACL resource', [])
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Create a new integration');

        $help = <<<HELP
Creates a new integration e.g. for 3rd party applications.

<info>Keys and Secets</info>
Keys and secrets are generated by Magento if not defined by specify options.
If key or a secret is defined manually, you must guarantee that this key or secret is at least 32 characters long
and unique in the system.

<info>Permissions</info>
If you do not specify ACL resources by resource option, the new integration will get ALL permissions.
To see a list of all available ACL resources you can run the <comment>config:data:acl</comment> command.

HELP;
        $this->setHelp($help);
    }

    public function inject(
        IntegrationFactory        $integrationFactory,
        IntegrationService        $integrationService,
        Consumer                                             $consumerResource,
        OauthService                                         $oauthService,
        AuthorizationService                                 $authorizationService,
        TokenFactory                                         $tokenFactory,
        \Magento\Integration\Model\ResourceModel\Oauth\Token $tokenResource
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->tokenFactory = $tokenFactory;
        $this->consumerResource = $consumerResource;
        $this->tokenResource = $tokenResource;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $integrationName = $input->getArgument('name');
        $integrationEmail = $input->getArgument('email');
        $integrationEndpoint = $input->getArgument('endpoint');
        $consumerKey = $input->getOption('consumer-key');
        $consumerSecret = $input->getOption('consumer-secret');
        $accessToken = $input->getOption('access-token');
        $accessTokenSecret = $input->getOption('access-token-secret');
        $grantedResources = $input->getOption('resource');

        // Validate email
        if ($integrationEmail !== null && !filter_var($integrationEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email address');
        }

        // Validate URL
        if ($integrationEndpoint !== null && !filter_var($integrationEndpoint, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Invalid endpoint URL');
        }

        $integrationModel = $this->createIntegration($integrationName, $integrationEmail, $integrationEndpoint);
        $consumerModel = $this->saveConsumer($integrationModel, $consumerKey, $consumerSecret);
        $this->grantPermissions($integrationModel, $grantedResources);
        $tokenModel = $this->activateToken($integrationModel, $accessToken, $accessTokenSecret);

        $data = array_merge(
            $this->getIntegrationData($integrationModel),
            $this->getConsumerData($consumerModel),
            $this->getTokenData($tokenModel)
        );

        $table = [];

        foreach ($data as $key => $value) {
            $table[] = [$key, $value];
        }

        $this->getHelper('table')
            ->setHeaders(['name', 'value'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }

    /**
     * @param IntegrationAlias $integrationModel
     * @param $accessToken
     * @param $accessTokenSecret
     * @return Token
     * @throws AlreadyExistsException
     */
    private function activateToken(IntegrationAlias $integrationModel, $accessToken, $accessTokenSecret): Token
    {
        /** @var Token $tokenModel */
        $tokenModel = $this->tokenFactory->create();
        $tokenModel->createVerifierToken($integrationModel->getConsumerId());
        $tokenModel->setType(Token::TYPE_ACCESS);

        if ($accessToken !== null) {
            $tokenModel->setToken($accessToken);
        }
        if ($accessTokenSecret !== null) {
            $tokenModel->setSecret($accessTokenSecret);
        }
        $this->tokenResource->save($tokenModel);
        return $tokenModel;
    }

    /**
     * @param IntegrationAlias $integrationModel
     * @param $consumerKey
     * @param $consumerSecret
     * @return ConsumerModel
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws Exception
     */
    private function saveConsumer(IntegrationAlias $integrationModel, $consumerKey, $consumerSecret): ConsumerModel
    {
        $consumerModel = $this->oauthService->loadConsumer($integrationModel->getConsumerId());
        if ($consumerKey !== null) {
            $consumerModel->setKey($consumerKey);
        }
        if ($consumerSecret !== null) {
            $consumerModel->setSecret($consumerSecret);
        }
        $this->consumerResource->save($consumerModel);
        return $consumerModel;
    }

    /**
     * @param $integrationName
     * @param $integrationEmail
     * @param $integrationEndpoint
     * @return IntegrationAlias
     * @throws IntegrationException
     */
    private function createIntegration($integrationName, $integrationEmail, $integrationEndpoint): IntegrationAlias
    {
        $integrationModel = $this->integrationService->findByName($integrationName);

        if ($integrationModel->getId() > 0) {
            throw new RuntimeException('An integration with that name already exists');
        }

        $integrationModel = $this->integrationService->create([
            'name' => $integrationName,
            'email' => $integrationEmail,
            'status' => IntegrationAlias::STATUS_ACTIVE,
            'endpoint' => $integrationEndpoint,
            'setup_type' => IntegrationAlias::TYPE_MANUAL
        ]);
        return $integrationModel;
    }

    /**
     * @param IntegrationAlias $integrationModel
     * @param array $grantedResources
     * @throws LocalizedException
     */
    private function grantPermissions(IntegrationAlias $integrationModel, array $grantedResources)
    {
        if (empty($grantedResources)) {
            $this->authorizationService->grantAllPermissions($integrationModel->getId());
        } else {
            $this->authorizationService->grantPermissions($integrationModel->getId(), $grantedResources);
        }
    }
}
