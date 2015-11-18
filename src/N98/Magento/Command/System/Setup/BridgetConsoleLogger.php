<?php

namespace N98\Magento\Command\System\Setup;

use Magento\Framework\Setup\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BridgetConsoleLogger implements LoggerInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Logs success message
     *
     * @param string $message
     *
     * @return void
     */
    public function logSuccess($message)
    {
        $this->output->writeln('<info>' . $message . '</info>');
    }

    /**
     * Logs error message
     *
     * @param \Exception $e
     *
     * @return void
     */
    public function logError(\Exception $e)
    {
        $this->output->writeln('<error>' . $e->getMessage() . '</error>');
    }

    /**
     * Logs a message
     *
     * @param string $message
     *
     * @return void
     */
    public function log($message)
    {
        $this->output->writeln('<info>' . $message . '</info>');
    }

    /**
     * Logs a message in the current line
     *
     * @param string $message
     *
     * @return void
     */
    public function logInline($message)
    {
        $this->output->write($message);
    }

    /**
     * Logs meta information
     *
     * @param string $message
     *
     * @return void
     */
    public function logMeta($message)
    {
        $this->output->writeln($message);
    }
}
