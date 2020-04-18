<?php

namespace N98\Magento\Command\Config\Env;

/**
 * Class EnvHelper
 * @package N98\Magento\Command\Config\Env
 */
class EnvHelper
{
    /**
     * @param $var
     * @param string $indent
     * @return string|null
     */
    public static function exportVariable($var, $indent='')
    {
        switch (gettype($var)) {
            case 'string':
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';

            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = $indent . '    '
                        . ($indexed ? '' : self::exportVariable($key) . ' => ')
                        . self::exportVariable($value, $indent . '    ');
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";

            case 'boolean':
                return $var ? 'TRUE' : 'FALSE';

            default:
                return var_export($var, true);
        }
    }
}
