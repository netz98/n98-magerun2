<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Integration;

use Exception;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration as IntegrationAlias;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\IntegrationService;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteCommand
 * @package N98\Magento\Command\Integration
 */
class DeleteCommand extends AbstractMagentoCommand
{
    /**
     * @var IntegrationService
     */
    private $integrationService;

    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    protected function configure()
    {
        $this
            ->setName('integration:delete')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name or ID of the integration')
            ->setDescription('Delete an existing integration.');
    }

    public function inject(
        IntegrationService $integrationService,
        OauthServiceInterface $oauthService,
        IntegrationFactory $integrationFactory
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->integrationFactory = $integrationFactory;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $integrationName = $input->getArgument('name');
        $selectedInteractively = false;

        if ($integrationName === null || $integrationName === '') {
            $integrationName = $this->selectIntegration($output);
            if ($integrationName === null) {
                return Command::SUCCESS;
            }
            $selectedInteractively = true;
        }

        $integrationModel = $this->integrationService->findByName($integrationName);

        if ($integrationModel->getId() <= 0 && is_numeric($integrationName)) {
            $integrationModel = $this->integrationService->get($integrationName);
        }

        if ($integrationModel->getId() <= 0) {
            throw new RuntimeException('Integration with this name or ID does not exist.');
        }

        if ($selectedInteractively && !confirm(
            sprintf(
                '<question>Are you sure you want to delete integration "%s" (ID: %d)?</question>',
                $integrationModel->getName(),
                $integrationModel->getId()
            ),
            false
        )) {
            $output->writeln('<error>Operation cancelled.</error>');
            return Command::FAILURE;
        }

        $this->integrationService->delete($integrationModel->getId());

        /**
         * we have to delete the consumer entry, because there is no way
         * reference on the database with cascade delete
         *
         * @see https://github.com/netz98/n98-magerun2/issues/1287
         */
        $this->oauthService->deleteConsumer($integrationModel->getConsumerId());

        $output->writeln(
            sprintf(
                '<info>Successfully deleted integration <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $integrationModel->getName(),
                $integrationModel->getId()
            )
        );

        return Command::SUCCESS;
    }

    /**
     * @return string|null Name of the selected integration, or null if there was nothing to select
     */
    private function selectIntegration(OutputInterface $output): ?string
    {
        $integrations = $this->integrationFactory->create()->getCollection()->getItems();

        if (count($integrations) === 0) {
            $output->writeln('<info>No integrations found.</info>');
            return null;
        }

        $options = [];
        /** @var IntegrationAlias $integration */
        foreach ($integrations as $integration) {
            $options[$integration->getName()] = sprintf(
                '%s (ID: %d, email: %s)',
                $integration->getName(),
                $integration->getId(),
                $integration->getEmail() ?: '-'
            );
        }

        return select('<question>Select an integration to delete:</question>', $options);
    }
}
