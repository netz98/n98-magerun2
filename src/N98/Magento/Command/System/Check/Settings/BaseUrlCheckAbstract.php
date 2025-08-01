<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <mot@fsfe.org>
 */

namespace N98\Magento\Command\System\Check\Settings;

use Magento\Store\Api\Data\StoreInterface;
use N98\Magento\Command\System\Check\Result;

/**
 * Class BaseUrlCheckAbstract
 *
 * @package N98\Magento\Command\System\Check\Settings
 */
abstract class BaseUrlCheckAbstract extends CheckAbstract
{
    protected $class = 'abstract';

    public function initConfigPaths()
    {
        $this->registerStoreConfigPath('baseUrl', 'web/' . $this->class . '/base_url');
    }

    /**
     * @param Result $result
     * @param StoreInterface $store
     * @param string $baseUrl setting
     */
    protected function checkSettings(Result $result, StoreInterface $store, $baseUrl)
    {
        $errorMessage = 'Wrong hostname configured. <info>Hostname must contain a dot</info>';

        $host = parse_url($baseUrl, PHP_URL_HOST);
        $isValid = (bool) strstr($host, '.');
        $result->setStatus($isValid);
        if ($isValid) {
            $result->setMessage(
                '<info>' . ucfirst($this->class) . ' BaseURL: <comment>' . $baseUrl .
                '</comment> of Store: <comment>' . $store->getCode() . '</comment> - OK'
            );
        } else {
            $result->setMessage(
                '<error>Invalid ' . ucfirst($this->class) . ' BaseURL: <comment>' . $baseUrl .
                '</comment> of Store: <comment>' . $store->getCode() . '</comment> ' . $errorMessage . '</error>'
            );
        }
    }
}
