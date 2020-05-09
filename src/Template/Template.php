<?php

namespace TorrentPier\Template;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Wrapper for the obsolete template engine
 */
class Template
{
    use LegacyApiTrait;

    /**
     * @var Twig_Environment
     */
    protected $engine;

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * Template constructor.
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->engine = $twig;
    }

    /**
     * @param string $dir
     * @param string $namespace
     */
    public function addDirectory(string $dir, string $namespace = '__main__'): void
    {
        $this->engine->getLoader()->addPath($dir, $namespace);
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function addTemplate(string $name, string $path): void
    {
        $this->templates[$name] = $path;
    }

    /**
     * @param string $templateName
     * @param array $contextData
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function display(string $templateName, array $contextData = []): void
    {
        $this->engine->display($this->templates[$templateName], $contextData);
    }
}
