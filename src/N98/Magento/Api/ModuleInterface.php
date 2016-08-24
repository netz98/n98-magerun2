<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */


namespace N98\Magento\Api;

/**
 * Magento Module
 *
 * @package N98\Magento\Api
 */
interface ModuleInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getVersion();
}
