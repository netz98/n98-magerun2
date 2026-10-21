<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Class\Plugin;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

class PluginInspector
{
    /** @return array{0: ReflectionClass, 1: ?string} */
    public function parseTarget(string $target): array
    {
        if (!preg_match('/^\\\\?[a-zA-Z_][a-zA-Z0-9_\\\\]*(?:::([a-zA-Z_][a-zA-Z0-9_]*)\\(\\))?$/D', $target, $matches)) {
            throw new InvalidArgumentException('Expected a class name or Class::method().');
        }
        $className = ltrim(explode('::', $target)[0], '\\');
        if (!class_exists($className) && !interface_exists($className)) {
            throw new InvalidArgumentException('Unknown class or interface: ' . $className);
        }
        $class = new ReflectionClass($className);
        $method = $matches[1] ?? null;
        if ($method !== null) {
            if (!$class->hasMethod($method) || !$this->isObservable($class->getMethod($method))) {
                throw new InvalidArgumentException('Method must exist and be public, non-static, non-final and not a constructor: ' . $method);
            }
            $method = $class->getMethod($method)->getName();
        }

        return [$class, $method];
    }

    /**
     * Inspect merged DI XML data without creating subjects or plugin instances.
     *
     * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: int, 5: string}>
     */
    public function inspect(ReflectionClass $class, ?string $method, array $config, callable $resolveType): array
    {
        $plugins = $this->inheritPlugins($class, $config);
        $rows = [];
        foreach ($plugins as $name => $plugin) {
            $instance = $plugin['instance'] ?? '';
            $order = (int) ($plugin['sortOrder'] ?? 0);
            $status = !empty($plugin['disabled']) ? 'disabled' : 'active';
            $pluginClass = $instance !== '' ? $resolveType($instance) : '';
            if ($pluginClass === '' || !class_exists($pluginClass)) {
                // Disabled declarations may have no instance, or reference an unavailable class.
                if ($method === null) {
                    $rows[] = [(string) $name, $instance, '-', '-', $order, $status];
                }
                continue;
            }
            foreach ((new ReflectionClass($pluginClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $pluginMethod) {
                if ($pluginMethod->isStatic() || !preg_match('/^(before|around|after)(.+)$/D', $pluginMethod->getName(), $matches)) {
                    continue;
                }
                $observed = lcfirst($matches[2]);
                if (!$class->hasMethod($observed)) {
                    continue;
                }
                $subjectMethod = $class->getMethod($observed);
                $observed = $subjectMethod->getName();
                if (!$this->isObservable($subjectMethod) || ($method !== null && $method !== $observed)) {
                    continue;
                }
                $rows[] = [(string) $name, $instance, $observed, $matches[1], $order, $status];
            }
        }
        usort($rows, static function (array $left, array $right): int {
            return ($left[4] <=> $right[4]) ?: ([$left[0], $left[2], $left[3], $left[1]] <=> [$right[0], $right[2], $right[3], $right[1]]);
        });

        return $rows;
    }

    private function isObservable(ReflectionMethod $method): bool
    {
        return $method->isPublic() && !$method->isStatic() && !$method->isFinal()
            && !$method->isConstructor() && !$method->isDestructor();
    }

    private function inheritPlugins(ReflectionClass $class, array $config): array
    {
        $plugins = [];
        $parent = $class->getParentClass();
        if ($parent !== false) {
            $plugins = $this->inheritPlugins($parent, $config);
        }
        foreach ($class->getInterfaces() as $interface) {
            if ($parent !== false && $parent->implementsInterface($interface->getName())) {
                continue;
            }
            $plugins = array_replace_recursive($plugins, $config[$interface->getName()]['plugins'] ?? []);
        }

        return array_replace_recursive($plugins, $config[$class->getName()]['plugins'] ?? []);
    }
}
