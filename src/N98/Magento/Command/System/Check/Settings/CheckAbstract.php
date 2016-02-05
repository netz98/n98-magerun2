<?php
/*
 * @author Tom Klingenberg <mot@fsfe.org>
 */

namespace N98\Magento\Command\System\Check\Settings;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;

/**
 * Class CheckAbstract
 *
 * @package N98\Magento\Command\System\Check\Settings
 */
abstract class CheckAbstract implements StoreCheck
{
    /**
     * @var array
     */
    private $storeConfigPaths = array();

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    final public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;

        $this->initConfigPaths();
    }

    abstract protected function initConfigPaths();

    /**
     * @param string $name
     * @param string $configPath
     */
    protected function registerStoreConfigPath($name, $configPath)
    {
        $this->storeConfigPaths[$name] = $configPath;
    }

    /**
     * @param ResultCollection       $results
     * @param StoreInterface $store
     *
     */
    public function check(ResultCollection $results, StoreInterface $store)
    {
        $result = $results->createResult();

        $typedParams = array(
            'result' => $result,
            'store'  => $store,
        );

        $paramValues = $this->getParamValues($store, $typedParams);


        $name       = 'checkSettings';
        $method     = new \ReflectionMethod($this, $name);
        $parameters = $method->getParameters();

        $arguments = array();
        foreach ($parameters as $parameter) {
            $paramName  = $parameter->getName();
            $paramClass = $parameter->getClass();

            // create named parameter from type-hint if applicable
            if ($paramClass) {
                foreach ($typedParams as $object) {
                    if ($paramClass->isSubclassOf(get_class($object))) {
                        $paramValues[$paramName] = $object;
                        break;
                    }
                }
            }

            // use named parameter, otherwise null
            $paramValues += array($paramName => null);
            $arguments[] = $paramValues[$paramName];
        }

        call_user_func_array(array($this, $name), $arguments);
    }

    /**
     * @param StoreInterface $store
     * @param array $typedParams
     * @return array
     */
    private function getParamValues(StoreInterface $store, array $typedParams)
    {
        $paramValues = $this->storeConfigPaths;

        foreach ($paramValues as $name => $path) {
            $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode());
            $paramValues[$name] = $value;
        }

        $paramValues = $typedParams + $paramValues;

        return $paramValues;
    }
}
