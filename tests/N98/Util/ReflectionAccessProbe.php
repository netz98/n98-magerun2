<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

/** Dependency-free probe, also runnable directly with a newer host PHP binary. */
class ReflectionAccessProbe
{
    public static function verify(): array
    {
        $object = new class() {
            private string $privateValue = 'private';
            protected string $protectedValue = 'protected';
            private static string $staticValue = 'static';

            private function readPrivate(): string
            {
                return $this->privateValue;
            }

            protected function readProtected(): string
            {
                return $this->protectedValue;
            }

            protected static function readStatic(): string
            {
                return self::$staticValue;
            }
        };
        $reflection = new \ReflectionObject($object);
        $results = [];
        foreach (['private' => 'readPrivate', 'protected' => 'readProtected'] as $visibility => $method) {
            $property = $reflection->getProperty($visibility . 'Value');
            $results[$visibility . '_read'] = $property->getValue($object);
            $property->setValue($object, $visibility . ' changed');
            $results[$visibility . '_invoke'] = $reflection->getMethod($method)->invoke($object);
        }
        $static = $reflection->getProperty('staticValue');
        $results['static_read'] = $static->getValue();
        $static->setValue(null, 'static changed');
        $results['static_invoke'] = $reflection->getMethod('readStatic')->invoke(null);
        if ($results !== self::expected()) {
            throw new \RuntimeException('Non-public reflection access did not preserve read/write/invoke behavior.');
        }
        return $results;
    }

    public static function expected(): array
    {
        return [
            'private_read' => 'private', 'private_invoke' => 'private changed',
            'protected_read' => 'protected', 'protected_invoke' => 'protected changed',
            'static_read' => 'static', 'static_invoke' => 'static changed',
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo json_encode(['php' => PHP_VERSION, 'results' => ReflectionAccessProbe::verify()], JSON_THROW_ON_ERROR) . PHP_EOL;
}
