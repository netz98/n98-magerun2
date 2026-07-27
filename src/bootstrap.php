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
    // Keep in sync with the "php" constraint in composer.json.
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        throw new \ErrorException('PHP Version is lower than 8.0.0. Please upgrade your runtime.');
    }
    return N98\MagerunBootstrap::createApplication();
} catch (Throwable $e) {
    $verbose = (bool) array_intersect(['-vvv', '-vv', '-v', '--verbose'], $argv);

    // Bootstrapping is where the autoloader itself can be broken, so the styled renderer is only
    // used when its classes are actually loadable; otherwise fall back to unformatted output.
    if (class_exists(N98\Magento\Application\Console\ErrorRenderer::class)) {
        $output = new Symfony\Component\Console\Output\ConsoleOutput(
            $verbose
                ? Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE
                : Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL
        );

        (new N98\Magento\Application\Console\ErrorRenderer())->render($e, $output->getErrorOutput());
    } else {
        fprintf(STDERR, "%s: %s\n", get_class($e), $e->getMessage());
        if ($verbose) {
            fprintf(STDERR, "%s\n", $e->getTraceAsString());
        }
    }

    exit(1);
}
