<?php

namespace TorrentPier\Template;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Wrapper for the obsolete template engine
 */
class Template
{
    use LegacyApiTrait;

    /**
     * @var Environment
     */
    protected $engine;

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * Template constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function display(string $templateName, array $contextData = []): void
    {
        $this->engine->display($this->templates[$templateName], $contextData);
    }
}
