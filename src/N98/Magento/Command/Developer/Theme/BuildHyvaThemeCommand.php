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
use Symfony\Component\Console\Command\Command;
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
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Build all Hyvä themes'
            )
            ->addOption(
                'suppress-no-theme-found-error',
                null,
                InputOption::VALUE_NONE,
                'Suppress error if no Hyvä theme was found'
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
    ) {
        $this->themeCollection = $themeCollection;
        $this->state = $state;
        $this->componentRegistrar = $componentRegistrar;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            // if --all option is set, we don't need a theme argument
            $input->setArgument('theme', null);
            $input->setOption('production', true);
            return;
        }

        if (!$input->getArgument('theme')) {
            // use theme collection to get all themes and then select one

            $themes = $this->themeCollection->getItems();
            $themePaths = array_map(function ($theme) {
                /* @var \Magento\Theme\Model\Theme $theme */
                return $theme->getFullPath();
            }, $themes);

            $themePaths = array_filter($themePaths, function ($themePath) {
                return $this->isHyvaTheme($themePath);
            });

            // start index with 0
            $themePaths = array_values($themePaths);

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
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->initMagento();

        $this->state->setAreaCode(Area::AREA_FRONTEND);

        $themePath = $input->getArgument('theme');

        if (empty($themePath) && !$input->getOption('all')) {
            throw new InvalidArgumentException('Theme path is required. Add theme as first argument.');
        }

        if ($input->getOption('all')) {
            // Build all Hyvä themes
            $themes = $this->themeCollection->getItems();
            $themePaths = array_map(function ($theme) {
                return $theme->getFullPath();
            }, $themes);
            $themePaths = array_filter($themePaths, function ($themePath) {
                return $this->isHyvaTheme($themePath);
            });
            $themePaths = array_values($themePaths);

            /**
             * Check if there are any Hyvä themes available.
             */
            if (empty($themePaths)) {
                if ($input->getOption('suppress-no-theme-found-error')) {
                    return Command::SUCCESS;
                }
                $output->writeln('<error>No Hyvä themes found.</error>');

                return Command::FAILURE;
            }

            $result = Command::SUCCESS;
            foreach ($themePaths as $path) {
                $statusCode = $this->buildTheme($path, $output, $input);
                if ($statusCode !== Command::SUCCESS) {
                    $output->writeln(
                        sprintf(
                            '<error>Build of theme "%s" failed with status code: %d</error>',
                            $path,
                            $result
                        )
                    );
                    $result = Command::FAILURE;
                }
            }

            return $result;
        }

        if (!$input->getOption('all')) {
            $result = $this->buildTheme($themePath, $output, $input);
        }

        return $result;

    }

    /**
     * @param string $themePath
     * @return bool
     */
    private function isHyvaTheme(string $themePath): bool
    {
        // if directory "web/tailwind" does not exist, skip this theme
        $themeDir = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themePath);

        return is_dir($themeDir . '/web/tailwind');
    }

    /**
     * @param mixed $themePath
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return int|null
     */
    protected function buildTheme(mixed $themePath, OutputInterface $output, InputInterface $input): ?int
    {
        if (!$this->isHyvaTheme($themePath)) {
            throw new InvalidArgumentException(sprintf('Theme "%s" is not a Hyvä theme.', $themePath));
        }

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
            $process->setTimeout(3600); // 1 hour timeout
            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException($process->getErrorOutput());
            }
        }

        $buildNpmCommand = 'watch'; // Default is watch mode
        if ($isProduction) {
            $buildNpmCommand = 'build-prod';
        }

        $process = new Process(['npm', 'run', $buildNpmCommand]);
        $process->setWorkingDirectory($webTailwindDirInTheme);
        $process->setTty(true);
        $process->setTimeout(3600 * 2); // 2 hours timeout for production builds
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getExitCode();
    }
}
