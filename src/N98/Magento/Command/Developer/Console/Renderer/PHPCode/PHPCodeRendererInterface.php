<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Renderer\PHPCode;

/**
 * Interface PHPCodeRendererInterface
 * @package N98\Magento\Command\Developer\Console\Renderer\PHPCode
 */
interface PHPCodeRendererInterface
{
    /**
     * @return string
     */
    public function render();
}
