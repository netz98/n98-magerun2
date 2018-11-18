<?php

namespace N98\Util\Console\Helper;

use Exception;
use N98\Util\Template\Twig;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper to render twig templates
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
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render($template, $variables = array())
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
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderString($string, $variables = array())
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
