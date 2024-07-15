<?php

declare(strict_types=1);

namespace N98\Magento\Command\System\Check\Hyva;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use N98\Magento\Command\CommandAware;
use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\System\Check\ProjectComposerTrait;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\WebsiteCheck;
use Symfony\Component\Console\Command\Command;

class IsCaptchaEnabledCheck implements WebsiteCheck, CommandAware, CommandConfigAware
{
    use HyvaTrait;
    use ProjectComposerTrait;

    /**
     * @var array
     */
    protected $commandConfig;

    /**
     * @var \N98\Magento\Command\System\CheckCommand
     */
    protected $command;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \N98\Magento\Command\System\Check\ResultCollection $results
     * @param \Magento\Store\Api\Data\WebsiteInterface $website
     * @return \N98\Magento\Command\System\Check\Result
     * @throws \JsonException
     */
    public function check(ResultCollection $results, WebsiteInterface $website)
    {
        $result = $results->createResult();

        $magentoRootFolder = $this->command->getApplication()->getMagentoRootFolder();
        $projectComposerPackages = $this->getProjectComposerPackages($results, $magentoRootFolder);

        if (!$this->isHyvaAvailable($projectComposerPackages, $this->commandConfig)) {
            $result->setMessage('Incompatible Magento Default Captcha is enabled');
            $result->setStatus(Result::STATUS_SKIPPED);

            return $result;
        }

        $isEnabled = $this->scopeConfig->isSetFlag(
            'customer/captcha/enable',
            ScopeInterface::SCOPE_WEBSITE,
            $website->getCode()
        );

        if ($isEnabled) {
            $result->setMessage(
                '<error>Incompatible Magento Default Captcha in website ' .
                '<comment>' . $website->getCode() . '</comment> is active!</error>'
            );
        } else {
            $result->setMessage(
                '<info>Incompatible Magento Default Captcha in website ' .
                '<comment>' . $website->getCode() . '</comment> is disabled.</info>'
            );
        }

        return $result;
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->commandConfig = $commandConfig;
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }
}
