<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

/**
 * Class VariablesCommand
 * @package N98\Magento\Command\Database
 */
class VariablesCommand extends AbstractShowCommand
{
    /**
     * variable name => recommended size (but this value must be calculated depending on the server size
     * @see https://launchpadlibrarian.net/78745738/tuning-primer.sh convert that to PHP ... ?
     *      http://www.slideshare.net/shinguz/mysql-configuration-the-most-important-variables GERMAN
     * @var array
     */
    protected $_importantVars = [
        'have_query_cache'                => [
            'desc' => 'YES if the mysqld binary is compiled with query cache support, NO otherwise.',
        ],
        'innodb_additional_mem_pool_size' => [
            'desc' => 'The size in bytes of a memory pool used for storing information about InnoDB tables and indexes.',
        ],
        'innodb_buffer_pool_size'         => [
            'desc' => 'The size in bytes of the buffer pool, the memory area where InnoDB caches table and index data.',
        ],
        'innodb_log_buffer_size'          => [
            'desc' => 'The size in bytes of the buffer that InnoDB uses to write to the log files on disk.',
        ],
        'innodb_log_file_size'            => [
            'desc' => 'The size in bytes of each log file in the log group.',
        ],
        'innodb_thread_concurrency'       => [
            'desc' => 'Defines the maximum number of threads permitted inside of InnoDB.',
        ],
        'join_buffer_size'                => [
            'desc' => 'The minimum size of the buffer that is used for plain index scans, range index scans, and joins that do not use indexes and thus perform full table scans.',
        ],
        'key_buffer_size'                 => [
            'desc' => 'Index blocks for MyISAM tables are buffered and are shared by all threads.',
        ],
        'max_allowed_packet'              => [
            'desc' => 'The maximum size of one packet or any generated/intermediate string.',
        ],
        'max_connections'                 => [
            'desc' => 'The maximum permitted number of simultaneous client connections.',
        ],
        'max_heap_table_size'             => [
            'desc' => 'This variable sets the maximum size to which user-created MEMORY tables are permitted to grow.',
        ],
        'open_files_limit'                => [
            'desc' => 'The number of file descriptors available to mysqld from the operating system.',
        ],
        'query_cache_size'                => [
            'desc' => 'The amount of memory allocated for caching query results.',
        ],
        'query_cache_type'                => [
            'desc' => 'Set the query cache type.',
        ],
        'read_rnd_buffer_size'            => [
            'desc' => 'When reading rows in sorted order following a key-sorting operation, the rows are read through this buffer to avoid disk seeks.',
        ],
        'read_buffer_size'                => [
            'desc' => 'Each thread that does a sequential scan for a MyISAM table allocates a buffer of this size (in bytes) for each table it scans.',
        ],
        'sort_buffer_size'                => [
            'desc' => 'Each session that must perform a sort allocates a buffer of this size.',
        ],
        'table_definition_cache'          => [
            'desc' => 'The number of table definitions (from .frm files) that can be stored in the definition cache.',
        ],
        'table_open_cache'                => [
            'desc' => 'The number of open tables for all threads.',
        ],
        'thread_cache_size'               => [
            'desc' => 'How many threads the server should cache for reuse.',
        ],
        'tmp_table_size'                  => [
            'desc' => 'The maximum size of internal in-memory temporary tables.',
            'opt'  => '', // @todo calculate somehow the optimal values depending on the MySQL server environment
        ],
    ];

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:variables')
            ->setDescription('Shows important variables or custom selected');

        $help = <<<HELP
This command is useful to print all important variables about the current database.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function allowRounding($name)
    {
        $toHuman = [
            'max_length_for_sort_data' => 1,
            'max_allowed_packet'       => 1,
            'max_seeks_for_key'        => 1,
            'max_write_lock_count'     => 1,
            'slave_max_allowed_packet' => 1,
        ];
        $isSize = false !== strpos($name, '_size');

        return $isSize || isset($toHuman[$name]);
    }
}
