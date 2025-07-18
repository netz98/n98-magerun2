<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

use PDO;

/**
 * Class Database
 * @package N98\Util
 */
class Database
{
    /**
     * @param PDO    $pdo
     * @param string $file
     * @param string $delimiter
     *
     * @return bool
     */
    public function importSqlDump(PDO $pdo, $file, $delimiter = ';')
    {
        set_time_limit(0);

        if (is_file($file) === true) {
            $file = \fopen($file, 'r');

            if (\is_resource($file) === true) {
                $query = [];

                while (feof($file) === false) {
                    $query[] = fgets($file);

                    if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                        $query = trim(implode('', $query));
                        $pdo->query($query);

                        while (ob_get_level() > 0) {
                            ob_end_flush();
                        }

                        flush();
                    }

                    if (is_string($query) === true) {
                        $query = [];
                    }
                }

                return fclose($file);
            }
        }

        return false;
    }
}
