<?php

namespace N98\Util\Template;

/**
 * Class Twig
 * @package N98\Util\Template
 */
class Twig
{
    /**
     * @var \Twig_Environment
     */
    protected $twigEnv;

    /**
     * @param array $baseDirs
     */
    public function __construct(array $baseDirs)
    {
        $loader = new \Twig\Loader\FilesystemLoader($baseDirs);
        $this->twigEnv = new \Twig\Environment($loader, ['debug' => true]);
        $this->addExtensions($this->twigEnv);
        $this->addFilters($this->twigEnv);
    }

    /**
     * @param string $filename
     * @param array $variables
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render($filename, $variables)
    {
        return $this->twigEnv->render($filename, $variables);
    }

    /**
     * @param string $string
     * @param array $variables
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderString($string, $variables)
    {
        $templates = ['runtime_template' => $string];
        $loader = new \Twig\Loader\ArrayLoader($templates);
        $twig = new \Twig\Environment($loader, ['debug' => true]);
        $this->addExtensions($twig);
        $this->addFilters($twig);

        return $twig->render('runtime_template', $variables);
    }

    /**
     * @param \Twig\Environment $twig
     */
    protected function addFilters(\Twig\Environment $twig)
    {
        /**
         * cast_to_array
         */
        $twig->addFilter(
            new \Twig\TwigFilter('cast_to_array', [$this, 'filterCastToArray'])
        );
    }

    /**
     * @param \Twig\Environment $twig
     */
    protected function addExtensions(\Twig\Environment $twig)
    {
        $twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    /**
     * @param mixed $stdClassObject
     *
     * @return array
     */
    public static function filterCastToArray($stdClassObject)
    {
        if (is_object($stdClassObject)) {
            $stdClassObject = get_object_vars($stdClassObject);
        }
        if (is_array($stdClassObject)) {
            return array_map(__METHOD__, $stdClassObject);
        } else {
            return $stdClassObject;
        }
    }
}
