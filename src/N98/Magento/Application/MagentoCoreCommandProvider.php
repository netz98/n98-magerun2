<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application;

use N98\Magento\Command\MagentoCoreProxyCommandFactory;
use N98\Util\OperatingSystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MagentoCoreCommandProvider
{
    /**
     * @var string
     */
    private $magentoRootDirectory;

    /**
     * @var MagentoCoreProxyCommandFactory
     */
    private $magentoCoreCommandFactory;

    /**
     * @var array
     */
    private $commandData;

    /**
     * @param string $magentoRootDirectory
     * @param MagentoCoreProxyCommandFactory $magentoCoreCommandFactory
     */
    public function __construct(
        string $magentoRootDirectory,
        MagentoCoreProxyCommandFactory $magentoCoreCommandFactory
    ) {
        $this->magentoRootDirectory = $magentoRootDirectory;
        $this->magentoCoreCommandFactory = $magentoCoreCommandFactory;
    }

    /**
     * @return array
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function getCommandNames(): array
    {
        $this->load();

        return array_column($this->commandData['commands'], 'name');
    }

    public function getCommands(): array
    {
        $this->load();

        $commands = [];

        foreach ($this->commandData as $commandData) {
            $commands[] = $this->magentoCoreCommandFactory->create(
                $this->magentoRootDirectory,
                $commandData['name'],
                $commandData['usage'],
                $commandData['description'],
                $commandData['help'],
                $commandData['definition']
            );
        }

        return $commands;
    }

    /**
     * @return void
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    private function load()
    {
        if (empty($this->commandData)) {
            $process = new Process(
                [OperatingSystem::getPhpBinary(), 'bin/magento', '--format=json'],
                $this->magentoRootDirectory
            );
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $data = \json_decode($process->getOutput(), true);

            // invalid JSON returned
            if (!$data) {
                throw new \RuntimeException(
                    'Cannot decode the result of bin/magento'
                );
            }

            // cleanup some commands -> first entry is the help command
            $this->commandData = $this->removeCommands($data['commands'], ['help', 'list']);
        }
    }

    /**
     * @param array $data
     * @param array $commandNamesToRemove
     * @return array
     */
    private function removeCommands(array $data, array $commandNamesToRemove)
    {
        return array_filter($data, function ($row) use ($commandNamesToRemove) {
            return !in_array($row['name'], $commandNamesToRemove, true);
        });
    }
}
