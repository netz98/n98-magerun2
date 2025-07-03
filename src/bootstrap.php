<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

if (!class_exists(N98\MagerunBootstrap::class)) {
    require_once __DIR__ . '/N98/MagerunBootstrap.php';
}

try {
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        throw new \ErrorException('PHP Version is lower than 7.4.0. Please upgrade your runtime.');
    }
    return N98\MagerunBootstrap::createApplication();
} catch (Exception $e) {
    printf("%s: %s\n", get_class($e), $e->getMessage());
    if (array_intersect(['-vvv', '-vv', '-v', '--verbose'], $argv)) {
        printf("%s\n", $e->getTraceAsString());
    }
    exit(1);
}
