<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Theme;

use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Developer\Theme
 */
class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var ThemeCollection
     */
    protected $themeCollection;

    protected function configure()
    {
        $this
            ->setName('dev:theme:list')
            ->setDescription('Lists all available themes')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
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
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $rows = [];

        foreach ($this->themeCollection as $theme) {
            $rows[] = [
                $theme->getId(),
                $theme->getThemePath(),
                $theme->getThemeTitle(),
                $theme->getArea(),
                $theme->getCode(),
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['id', 'path', 'title', 'area', 'code'])
            ->renderByFormat($output, $rows, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
