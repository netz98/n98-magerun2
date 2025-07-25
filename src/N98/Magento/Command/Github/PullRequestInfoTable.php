<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class PullRequestInfoTable
{
    /**
     * @param OutputInterface $output
     * @param array $prData
     * @return Table
     */
    public static function create(OutputInterface $output, array $prData): Table
    {
        $table = new Table($output);
        $table->addRow(['Number', $prData['number']]);
        $table->addRow(['Title', $prData['title']]);
        $table->addRow(['Created at', $prData['created_at']]);
        $table->addRow(['User', $prData['user']['login']]);
        $table->addRow(['State', $prData['state']]);
        $table->addRow(['URL', $prData['url']]);
        $table->addRow(['Diff-URL', $prData['diff_url']]);

        return $table;
    }
}
