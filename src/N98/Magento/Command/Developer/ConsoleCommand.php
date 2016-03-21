<?php

namespace N98\Magento\Command\Developer;

use Exception;
use Magento\Framework\App\ProductMetadataInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Unicode\Charset;
use PhpParser\Lexer;
use PhpParser\Parser;
use Psy\CodeCleaner;
use Psy\Command\ListCommand;
use Psy\Configuration;
use Psy\Output\ShellOutput;
use Psy\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psy\ParserFactory;

class ConsoleCommand extends AbstractMagentoCommand
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMeta;

    protected function configure()
    {
        $this
            ->setName('dev:console')
            ->setDescription(
                'Opens PHP interactive shell with initialized Mage::app() <comment>(Experimental)</comment>'
            )
        ;
    }

    /**
     * @param ProductMetadataInterface $productMetadata
     */
    public function inject(ProductMetadataInterface $productMetadata)
    {
        $this->productMeta = $productMetadata;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $initialized = false;
        try {
            $this->detectMagento($output);
            $initialized = $this->initMagento();
        } catch (Exception $e) {
            // do nothing
        }

        $parser = new Parser(new Lexer());
        $cleaner = new CodeCleaner($parser);
        $consoleOutput = new ShellOutput();
        $config = new Configuration();
        $config->setCodeCleaner($cleaner);
        $shell = new Shell($config);
        $shell->setScopeVariables([
            'di' => $this->getObjectManager(),
        ]);

        if ($initialized) {
            $ok = Charset::convertInteger(Charset::UNICODE_CHECKMARK_CHAR);

            $edition = $this->productMeta->getEdition();
            $magentoVersion = $this->productMeta->getVersion();

            $consoleOutput->writeln(
                '<fg=black;bg=green>Magento ' . $magentoVersion . ' ' . $edition .
                ' initialized.</fg=black;bg=green> ' . $ok
            );
        } else {
            $consoleOutput->writeln('<fg=black;bg=yellow>Magento is not initialized.</fg=black;bg=yellow>');
        }

        $help = <<<'help'
At the prompt, type <comment>help</comment> for some help.

To exit the shell, type <comment>^D</comment>.
help;

        $consoleOutput->writeln($help);

        $shell->run($input, $consoleOutput);
    }
}
