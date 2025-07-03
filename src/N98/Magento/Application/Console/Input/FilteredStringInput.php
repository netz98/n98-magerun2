<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console\Input;

use N98\Util\BinaryString;
use Symfony\Component\Console\Input\StringInput;

class FilteredStringInput extends StringInput
{
    protected function setTokens(array $tokens)
    {
        $tokenToFilter = [
            '--add-module-dir',
            '--root-dir',
            '--skip-root-check',
            '--skip-config',
            '--skip-core-commands',
            '--skip-magento-compatibility-check'
        ];

        foreach ($tokens as $key => $token) {
            $tokenName = current(BinaryString::trimExplodeEmpty('=', $token));

            if (in_array($tokenName, $tokenToFilter)) {
                unset($tokens[$key]);
            }
        }

        parent::setTokens($tokens);
    }
}
