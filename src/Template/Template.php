<?php

namespace TorrentPier\Template;

/**
 * Wrapper for the obsolete template engine
 *
 * @todo: need refactoring
 */
class Template
{
    use LegacyApiTrait;

    /**
     * @var \Twig_Environment
     */
    protected $engine;

    protected $templates = [];

    protected $data = [];

    public function __construct($twig)
    {
        $this->engine = $twig;
    }

    public function addDirectory($dir, $namespace)
    {
        $this->engine->getLoader()->addPath($dir, $namespace);
    }
}
