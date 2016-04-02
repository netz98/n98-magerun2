<?php

if (!class_exists('N98\MagerunBootstrap')) {
    require_once __DIR__ . '/N98/MagerunBootstrap.php';
}

try {
    if (version_compare(PHP_VERSION, '5.4.11', '<')) {
        throw new \ErrorException('PHP Version is lower than 5.4.11. Please upgrade your runtime.');
    }
    return N98\MagerunBootstrap::createApplication();
} catch (Exception $e) {
    printf("%s: %s\n", get_class($e), $e->getMessage());
    if (array_intersect(array('-vvv', '-vv', '-v', '--verbose'), $argv)) {
        printf("%s\n", $e->getTraceAsString());
    }
    exit(1);
}
