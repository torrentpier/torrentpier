<?php

namespace TorrentPier;

class View
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param $template
     * @param array $params
     * @return string
     */
    public function make($template, $params = [])
    {
        return $this->twig->render($template . '.twig' , $params);
    }
}