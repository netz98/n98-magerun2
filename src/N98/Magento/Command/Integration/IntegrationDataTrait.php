<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Integration;

use Magento\Integration\Model\Integration;

trait IntegrationDataTrait
{
    private function getIntegrationData($integrationModel): array
    {
        return [
            'Integration ID' => $integrationModel->getId(),
            'Name' => $integrationModel->getName(),
            'Email' => $integrationModel->getEmail(),
            'Endpoint' => $integrationModel->getEndpoint(),
            'Status' => $integrationModel->getStatus() == Integration::STATUS_ACTIVE ? 'Active' : 'Inactive',
        ];
    }

    private function getConsumerData($consumerModel): array
    {
        return [
            'Consumer Key' => $consumerModel->getKey(),
            'Consumer Secret' => $consumerModel->getSecret(),
        ];
    }

    private function getTokenData($tokenModel): array
    {
        return [
            'Access Token' => $tokenModel->getToken(),
            'Access Token Secret' => $tokenModel->getSecret(),
        ];
    }
}
