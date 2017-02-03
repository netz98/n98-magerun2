<?php

namespace N98\Magento\Command\Developer\Asset;

use Magento\Framework\App\View\Asset\Publisher as AssetPublisher;
use Magento\Framework\View\Asset\Repository as AssetRepo;

class ClearCommandTest extends TestCase
{
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
