<?php

namespace N98\Util\Console\Helper;

use Exception;
use N98\Magento\Command\CommandAware;
use N98\Util\Template\Twig;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper to render twig templates
 *
 * @package N98\Util\Console\Helper
 */
class TwigHelper extends Helper
{
    /**
     * @var \N98\Util\Template\Twig
     */
    protected $twig;

    /**
     * @param array $baseDirs
     * @throws RuntimeException
     */
    public function __construct(array $baseDirs)
    {
        try {
            $this->twig = new Twig($baseDirs);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Renders a twig template file
     *
     * @param string $template
     * @param array $variables
     * @return mixed
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render($template, $variables = [])
    {
        return $this->twig->render($template, $variables);
    }

    /**
     * Renders a twig string
     *
     * @param string $string
     * @param array $variables
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderString($string, $variables = [])
    {
        return $this->twig->renderString($string, $variables);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'twig';
    }
}
