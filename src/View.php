<?php

namespace TorrentPier;

class View
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * View constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function addGlobal($name, $value)
    {
        $this->twig->addGlobal($name, $value);
    }

    /**
     * @param $template
     * @param array $params
     * @return string
     */
    public function make($template, $params = [])
    {
        return $this->twig->render($template, $params);
    }
}
