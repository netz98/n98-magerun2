<?php

namespace N98\Magento\Command\Developer\Theme;

use InvalidArgumentException;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use N98\Magento\Command\AbstractMagentoCommand;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;

/**
 * Class BuildHyvaCommand
 * @package N98\Magento\Command\Developer\Theme
 */
class BuildHyvaThemeCommand extends AbstractMagentoCommand
{
    /**
     * @var ThemeCollection
     */
    protected ThemeCollection $themeCollection;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private ComponentRegistrarInterface $componentRegistrar;

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
            ->addArgument(
                'theme',
                InputArgument::OPTIONAL,
                'Hyvä Theme to build (e.g. Hyva/default)'
            );
    }

    /**
     * @param ThemeCollection $themeCollection
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     */
    public function inject(
        ThemeCollection $themeCollection,
        State $state,
        ComponentRegistrarInterface $componentRegistrar
    )
    {
        $this->themeCollection = $themeCollection;
        $this->state = $state;
        $this->componentRegistrar = $componentRegistrar;
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('theme')) {
            // use theme collection to get all themes and then select one

            $themes = $this->themeCollection->getItems();
            $themePaths = array_map(function ($theme) {
                /* @var \Magento\Theme\Model\Theme $theme */
                return $theme->getFullPath();
            }, $themes);

            $themePaths = array_filter($themePaths, function ($themePath) {
                if (str_starts_with($themePath, 'frontend/Magento')) {
                    return false;
                }

                if (str_starts_with($themePath, 'adminhtml/Magento')) {
                    return false;
                }

                // Improve this check -> find a better way to detect Hyva themes

                return true;
            });

            if (empty($themePaths)) {
                return;
            }

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion('Please select a theme to build', $themePaths);
            $themePath = $helper->ask($input, $output, $question);
            $input->setArgument('theme', $themePath);
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->initMagento();

        $this->state->setAreaCode(Area::AREA_FRONTEND);

        $themePath = $input->getArgument('theme');

        if (empty($themePath)) {
            throw new InvalidArgumentException('Theme path is required. Add theme as first argument.');
        }

        var_dump($themePath);

        // prefix the theme path with (=frontend|adminhtml) to get the full path then we prepend "frontend/"
        if (!str_starts_with($themePath, 'frontend/') && !str_starts_with($themePath, 'adminhtml/')) {
            $themePath = 'frontend/' . $themePath;
        }

        $theme = $this->themeCollection->getThemeByFullPath($themePath);
        if (!$theme) {
            throw new InvalidArgumentException(sprintf('Theme "%s" not found.', $themePath));
        }

        $themeDir = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themePath);

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln(sprintf('<debug>Theme Dir: <comment>%s</comment></debug>', $themeDir));
        }

        if (!is_dir($themeDir)) {
            throw new InvalidArgumentException(sprintf('Theme directory "%s" not found.', $themeDir));
        }

        $output->writeln(sprintf('<info>Building CSS for theme <comment>%s</comment>...</info>', $themePath));

        $isProduction = $input->getOption('production');

        if (!$isProduction) {
            $output->writeln(sprintf('<info>Watching for changes. Press Ctrl+C to stop.</info>'));
        }

        $webTailwindDirInTheme = $themeDir . '/web/tailwind';

        // Check if node_modules directory exists
        if (!is_dir($webTailwindDirInTheme . '/node_modules')) {
            $output->writeln('<info>Installing node modules...</info>');
            $process = new Process(['npm', 'install']);
            $process->setWorkingDirectory($webTailwindDirInTheme);
            $process->setTty(true);
            $process->run();
        }

        $buildNpmCommand = 'watch'; // Default is watch mode
        if ($isProduction) {
            $buildNpmCommand = 'build-prod';
        }

        $process = new Process(['npm', 'run', $buildNpmCommand]);
        $process->setWorkingDirectory($webTailwindDirInTheme);
        $process->setTty(true);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getExitCode();
    }
}
