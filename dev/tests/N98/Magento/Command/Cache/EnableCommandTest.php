<?php
namespace N98\Magento\Command\Cache;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class EnableCommandTest extends TestCase
{
    const NONEXISTENT_CACHE_TYPE = 'FAKE_CACHE_TYPE';
    /**
     * @var $command ListCommand
     */
    protected $command = null;

    public function setUp()
    {
        $application = $this->getApplication();
        $application->add(new EnableCommand);
        $application->add(new ListCommand);

        $this->command = $this->getApplication()->find('cache:enable');
    }

    /**
     * @todo writing solid unit tests for enabling caches is a bit hard to do giving environments differ
     * so for now, there is just one test to enable a nonexistent cache
     */

    public function testEnableNonexistentCache()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName(), 'type' => self::NONEXISTENT_CACHE_TYPE));

        echo $commandTester->getDisplay();

        // Strip tags because of console formatting (<info> etc)
        $expectedOutput = strip_tags(
            sprintf(EnableCommand::INVALID_TYPES_MESSAGE, self::NONEXISTENT_CACHE_TYPE) .
            PHP_EOL .
            EnableCommand::ABORT_MESSAGE .
            PHP_EOL
        );

        $this->assertEquals($expectedOutput, $commandTester->getDisplay());
    }
}