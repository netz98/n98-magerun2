<?php

namespace N98\Magento\Command;

/**
 * Class DummyCommand
 * @package N98\Magento\Command
 */
class DummyCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->setName('dummy');
    }
}
