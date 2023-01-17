<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console\Input;

use N98\Util\BinaryString;
use Symfony\Component\Console\Input\StringInput;

class FilteredStringInput extends StringInput
{
    protected function setTokens(array $tokens)
    {
        $tokenToFilter = ['--root-dir', '--skip-root-check', '--skip-config', '--skip-core-commands', '--skip-magento-compatibility-check'];

        foreach ($tokens as $key => $token) {
            $tokenName = current(BinaryString::trimExplodeEmpty('=', $token));

            if (in_array($tokenName, $tokenToFilter)) {
                unset($tokens[$key]);
            }
        }

        parent::setTokens($tokens);
    }
}
