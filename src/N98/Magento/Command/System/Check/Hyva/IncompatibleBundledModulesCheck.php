<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\System\Check\Hyva;

use Composer\Composer;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use N98\Magento\Command\CommandAware;
use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\System\Check\ProjectComposerTrait;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;
use Symfony\Component\Console\Command\Command;

class IncompatibleBundledModulesCheck implements SimpleCheck, CommandAware, CommandConfigAware
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
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $enabledModuleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(ModuleListInterface $moduleList, ProductMetadataInterface $productMetadata)
    {
        $this->enabledModuleList = $moduleList;
        $this->productMetadata = $productMetadata;
    }

    public function check(ResultCollection $results)
    {
        $result = $results->createResult();
        $result->setMessage('Incompatible bundled modules');

        $magentoRootFolder = $this->command->getApplication()->getMagentoRootFolder();
        $projectComposerPackages = $this->getProjectComposerPackages($results, $magentoRootFolder);

        if (!$this->isHyvaAvailable($projectComposerPackages, $this->commandConfig)) {
            $result->setStatus(Result::STATUS_SKIPPED);

            return $result;
        }

        $magentoVersion = $this->productMetadata->getVersion();
        $enabledModules = array_keys($this->enabledModuleList->getAll());
        $incompatibleBundledModulesExpressions = $this->commandConfig['hyva']['incompatible-bundled-modules'];

        foreach ($incompatibleBundledModulesExpressions as $expression => $incompatibleBundledModules) {
            if ($this->isSuitable($expression, $magentoVersion)) {
                $foundIncompatibleModules = array_intersect(
                    $enabledModules,
                    $incompatibleBundledModules
                );

                if (count($foundIncompatibleModules) === 0) {
                    $result->setStatus(Result::STATUS_OK);
                    $result->setMessage('No enabled incompatible bundled modules are found');
                } else {
                    $result->setStatus(Result::STATUS_ERROR);
                    $result->setMessage(
                        sprintf(
                            '<warning>Found incompatible bundled modules!</warning> (<comment>%s</comment>)',
                            implode(',', $foundIncompatibleModules)
                        )
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Evaluate Composer expression
     *
     * @param string $expression
     * @param string $magentoVersion
     * @return bool
     */
    private function isSuitable(string $expression, string $magentoVersion): bool
    {
        $versionParser = new VersionParser();
        $contraint = $versionParser->parseConstraints($expression);
        $magento = new Constraint('==', $versionParser->normalize($magentoVersion));

        return $contraint->matches($magento);
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
