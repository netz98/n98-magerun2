<?php

namespace N98\Magento\Command\System\Check\Settings;

/**
 * Class UnsecureCookieDomainCheck
 * @package N98\Magento\Command\System\Check\Settings
 */
class UnsecureCookieDomainCheck extends CookieDomainCheckAbstract
{
    /**
     * @var string
     */
    protected $class = 'unsecure';
}
