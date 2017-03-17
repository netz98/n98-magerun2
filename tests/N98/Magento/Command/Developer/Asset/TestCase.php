<?php

namespace N98\Magento\Command\Developer\Asset;

use Magento\Deploy\Model\Mode;
use Magento\Framework\App\State;
use N98\Magento\Command\TestCase as BaseTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return void
     */
    protected function setUp()
    {
        if ($this->runsInProductionMode()) {
            $this->markTestSkipped('This command is not available in production mode');
        }

        parent::setUp();
    }

    /**
     * @return bool
     */
    private function runsInProductionMode()
    {
        $objectManager = $this->getApplication()->getObjectManager();
        $mode = $objectManager->create(
            Mode::class,
            [
                'input'  => new ArgvInput(),
                'output' => new ConsoleOutput(),
            ]
        );

        return $mode->getMode() === State::MODE_PRODUCTION;
    }
}
