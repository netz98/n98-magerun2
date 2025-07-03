<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Integration;

use Exception;
use Magento\Integration\Api\OauthServiceInterface;
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
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    protected function configure()
    {
        $this
            ->setName('integration:delete')
            ->addArgument('name', InputArgument::REQUIRED, 'Name or ID of the integration')
            ->setDescription('Delete an existing integration.');
    }

    public function inject(
        IntegrationService $integrationService,
        OauthServiceInterface $oauthService
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
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

        $integrationModel = $this->integrationService->findByName($integrationName);

        if ($integrationModel->getId() <= 0 && is_numeric($integrationName)) {
            $integrationModel = $this->integrationService->get($integrationName);
        }

        if ($integrationModel->getId() <= 0) {
            throw new RuntimeException('Integration with this name or ID does not exist.');
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
}
