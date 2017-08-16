<?php

namespace N98\Magento\Framework;

interface AreaAware
{
    /**
     * Area to initialize
     * @return string
     */
    public function getAreaCode();
}
