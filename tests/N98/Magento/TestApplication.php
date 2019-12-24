<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Stub\ReturnStub as ReturnStubAlias;
use PHPUnit_Framework_MockObject_MockObject;
use RuntimeException;

/**
 * Magento test-application, the one used in unit and integration testing.
 *
 * @package N98\Magento
 */
class TestApplication
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var string|null
     */
    private $root;

    /**
     * @var string
     */
    private $varname;

    /**
     * @var string
     */
    private $basename;

    /**
     * @param string $varname name of the environment variable containing the test-root
     * @param string $basename name of the stopfile containing the test-root
     *
     * @return string|null
     */
    public static function getTestMagentoRootFromEnvironment($varname, $basename)
    {
        $root = getenv($varname);
        if (empty($root) && strlen($basename)) {
            $stopfile = getcwd() . '/' . $basename;
            if (is_readable($stopfile) && $buffer = rtrim(file_get_contents($stopfile))) {
                $root = $buffer;
            }
        }
        if (empty($root)) {
            return;
        }

        # directory test
        if (!is_dir($root)) {
            throw new RuntimeException(
                sprintf("%s path '%s' is not a directory", $varname, $root)
            );
        }

        # resolve root to realpath to be independent to current working directory
        $rootRealpath = realpath($root);
        if (false === $rootRealpath) {
            throw new RuntimeException(
                sprintf("Failed to resolve %s path '%s' with realpath()", $varname, $root)
            );
        }

        return $rootRealpath;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getConfig()
    {
        $testApplication = new TestApplication();
        $config = $testApplication->getApplication()->getConfig();

        return $config;
    }

    /**
     * TestApplication constructor.
     *
     * @param string $varname [optional] name of the environment variable containing the path to magento-root
     */
    public function __construct($varname = null, $basename = null)
    {
        if (null === $varname) {
            $varname = 'N98_MAGERUN2_TEST_MAGENTO_ROOT';
        }
        if (null === $basename) {
            $basename = '.n98-magerun2';
        }
        $this->varname = $varname;
        $this->basename = $basename;
    }

    /**
     * getter for the magento root directory of the test-suite
     *
     * @see ApplicationTest::testExecute
     *
     * @return string
     */
    public function getTestMagentoRoot()
    {
        if ($this->root) {
            return $this->root;
        }

        $varname = $this->varname;

        $root = self::getTestMagentoRootFromEnvironment($varname, $this->basename);

        if (null === $root) {
            $this->markTestSkipped(
                "Please specify environment variable $varname with path to your test magento installation!"
            );
        }

        return $this->root = $root;
    }

    /**
     * @return Application|PHPUnit_Framework_MockObject_MockObject
     * @throws \Exception
     */
    public function getApplication()
    {
        if ($this->application === null) {
            $root = $this->getTestMagentoRoot();

            $mockObjectGenerator = new Generator();

            /** @var Application|PHPUnit_Framework_MockObject_MockObject $application */
            $application = $mockObjectGenerator->getMock(\N98\Magento\Application::class, ['getMagentoRootFolder']);

            // Get the composer bootstraph
            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                $loader = require PHPUNIT_COMPOSER_INSTALL;
            } elseif (file_exists(__DIR__ . '/../../../../../autoload.php')) {
                // Installed via composer, already in vendor
                $loader = require __DIR__ . '/../../../../../autoload.php';
            } else {
                // Check if testing root package without PHPUnit
                $loader = require __DIR__ . '/../../../vendor/autoload.php';
            }

            /** @var $loader \Composer\Autoload\ClassLoader */

            $application->setAutoloader($loader);
            $application->method('getMagentoRootFolder')->willReturn($root);
            $application->init();
            $application->initMagento();

            $this->application = $application;
        }

        return $this->application;
    }

    /*
     * PHPUnit TestCase methods
     */

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed zero or more times.
     *
     * @return \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount
     * @since  Method available since Release 3.0.0
     */
    public static function any()
    {
        return new AnyInvokedCount();
    }

    /**
     *
     *
     * @param  mixed $value
     * @return \PHPUnit\Framework\MockObject\Stub\ReturnStub
     * @since  Method available since Release 3.0.0
     */
    public static function returnValue($value)
    {
        return new ReturnStubAlias($value);
    }

    /**
     * Mark the test as skipped.
     *
     * @param  string $message
     * @throws \PHPUnit\Framework\SkippedTestError
     * @since  Method available since Release 3.0.0
     */
    public static function markTestSkipped($message = '')
    {
        throw new \PHPUnit\Framework\SkippedTestError($message);
    }
}
