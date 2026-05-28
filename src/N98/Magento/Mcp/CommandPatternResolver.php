<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Mcp;

use RuntimeException;

class CommandPatternResolver
{
    /**
     * @param array<string, mixed> $commandConfig
     * @return array<string, array{commands: string[], description: string}>
     */
    public function getCommandGroupDefinitions(array $commandConfig): array
    {
        $groupDefinitions = [];
        if (!isset($commandConfig['command-groups'])) {
            return $groupDefinitions;
        }

        $groups = $commandConfig['command-groups'];
        foreach ($groups as $index => $definition) {
            if (!isset($definition['id'])) {
                throw new RuntimeException("Invalid definition of command-groups (id missing) at index: $index");
            }

            $id = $definition['id'];
            if (isset($groupDefinitions[$id])) {
                throw new RuntimeException("Invalid definition of command-groups (duplicate id) id: $id");
            }

            if (!isset($definition['commands'])) {
                throw new RuntimeException("Invalid definition of command-groups (commands missing) id: $id");
            }

            $commands = $definition['commands'];
            if (is_string($commands)) {
                $commands = preg_split('~\s+~', $commands, -1, PREG_SPLIT_NO_EMPTY);
            }
            if (!is_array($commands)) {
                throw new RuntimeException("Invalid commands definition of command-groups id: $id");
            }
            $commands = array_reduce((array) $commands, [$this, 'resolvePatternsArray'], null);

            $description = $definition['description'] ?? '';

            $groupDefinitions[$id] = [
                'commands' => $commands,
                'description' => $description,
            ];
        }

        return $groupDefinitions;
    }

    /**
     * @param string[] $list
     * @param array<string, array{commands: string[], description: string}> $definitions
     * @param array<string, bool> $resolved
     * @return string[]
     */
    public function resolvePatterns(array $list, array $definitions = [], array $resolved = []): array
    {
        $resolvedList = [];

        foreach ($list as $entry) {
            if (strpos($entry, '@') === 0) {
                $code = substr($entry, 1);
                if (!isset($definitions[$code])) {
                    throw new RuntimeException('Command-groups could not be resolved: ' . $entry);
                }

                if (!isset($resolved[$code])) {
                    $resolved[$code] = true;
                    array_push(
                        $resolvedList,
                        ...$this->resolvePatterns($definitions[$code]['commands'], $definitions, $resolved)
                    );
                }

                continue;
            }

            $resolvedList[] = $entry;
        }

        asort($resolvedList);

        return array_values(array_unique($resolvedList));
    }

    /**
     * @param string[]|null $carry
     * @param mixed $item
     * @return string[]
     */
    private function resolvePatternsArray(?array $carry = null, $item = null): array
    {
        if (is_string($item)) {
            $item = preg_split('~\s+~', $item, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!is_array($item)) {
            throw new RuntimeException(sprintf('Unable to handle %s', var_export($item, true)));
        }

        if (count($item) > 1) {
            $item = array_reduce($item, [$this, 'resolvePatternsArray'], (array) $carry);
        }

        return array_merge((array) $carry, $item);
    }
}
