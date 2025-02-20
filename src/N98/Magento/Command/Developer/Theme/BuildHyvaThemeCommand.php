<?php

namespace N98\Magento\Command\Developer\Theme;

use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildHyvaCommand
 * @package N98\Magento\Command\Developer\Theme
 */
class BuildHyvaThemeCommand extends AbstractMagentoCommand
{
    /**
     * @var ThemeCollection
     */
    protected $themeCollection;

    protected function configure()
    {
        $this
            ->setName('dev:theme:build-hyva')
            ->setDescription('Build Hyvä theme CSS')
            ->addOption(
                'production',
                'p',
                InputOption::VALUE_NONE,
                'Build for production (minified) instead of watch mode'
            )
            ->addOption(
                'theme',
                't',
                InputOption::VALUE_REQUIRED,
                'Hyvä Theme to build (e.g. Hyva/default)'
            );
    }

    /**
     * @param ThemeCollection $themeCollection
     */
    public function inject(ThemeCollection $themeCollection)
    {
        $this->themeCollection = $themeCollection;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $themePath = $input->getOption('theme');
        if (empty($themePath)) {
            throw new \InvalidArgumentException('Theme path is required. Use --theme option.');
        }

        $theme = $this->themeCollection->getThemeByFullPath($themePath);
        if (!$theme) {
            throw new \InvalidArgumentException(sprintf('Theme "%s" not found.', $themePath));
        }

        $themeDir = BP . '/app/design/frontend/' . $themePath;
        if (!is_dir($themeDir)) {
            throw new \InvalidArgumentException(sprintf('Theme directory "%s" not found.', $themeDir));
        }

        $isProduction = $input->getOption('production');

        $command = 'cd ' . $themeDir . ' && npm run watch'; // Default is watch mode

        if ($isProduction) {
            $command = 'cd ' . $themeDir . ' && npm run build-prod';
        }

        $output->writeln(sprintf('<info>Building CSS for theme "%s"...</info>', $themePath));

        if (!$isProduction) {
            $output->writeln(sprintf('<info>Watching for changes. Press Ctrl+C to stop.</info>'));
        }

        $process = proc_open($command, [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ], $pipes);

        return proc_close($process);
    }
}
