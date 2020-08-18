<?php

namespace N98\Magento\Command\Developer\Asset;

use Magento\Deploy\Model\Mode;
use Magento\Framework\App\State;
use Magento\Framework\App\View\Asset\Publisher as AssetPublisher;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use N98\Magento\Command\TestCase as BaseTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ClearCommandTest extends BaseTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
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

    /**
     * @return void
     */
    private function deployAsset()
    {
        $objectManager = $this->getApplication()->getObjectManager();
        $assetRepo = $objectManager->get(AssetRepo::class);
        $assetPublisher = $objectManager->create(AssetPublisher::class);
        $asset = $assetRepo->createAsset(
            'js/block-loader.js',
            [
                'area'   => 'frontend',
                'theme'  => 'Magento/blank',
                'locale' => 'en_US',
                'module' => 'Magento_Ui',
            ]
        );
        $assetPublisher->publish($asset);
    }

    /**
     * @return void
     */
    public function testExecuteClearAll()
    {
        $this->deployAsset();
        $this->assertDisplayContains(
            [
                'command' => 'dev:asset:clear',
            ],
            'static/frontend deleted'
        );
    }

    /**
     * @return void
     */
    public function testExecuteClearTheme()
    {
        $this->deployAsset();
        $this->assertDisplayContains(
            [
                'command' => 'dev:asset:clear',
                '--theme' => ['Magento/blank'],
            ],
            'static/frontend/Magento/blank'
        );
    }
}
