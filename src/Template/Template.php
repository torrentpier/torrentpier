<?php

namespace TorrentPier\Template;

/**
 * Wrapper for the obsolete template engine
 *
 * @todo: need refactoring
 */
class Template
{
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

    /**
     * @param $vars
     * @deprecated
     */
    public function assign_vars($vars)
    {
        foreach ($vars as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    /**
     * @param $key
     * @param $val
     * @deprecated
     */
    public function assign_var($key, $val = true)
    {
        $this->data[$key] = $val;
    }

    public function assign_block_vars($blockname, $vararray)
    {
        if (false !== strpos($blockname, '.')) {
            // Nested block.
            $blocks = explode('.', $blockname);
            $blockcount = \count($blocks) - 1;

            $str = &$this->data;
            for ($i = 0; $i < $blockcount; $i++) {
                $str = &$str[$blocks[$i] /*. '.'*/];
                $str = &$str[\count($str) - 1];
            }
            // Now we add the block that we're actually assigning to.
            // We're adding a new iteration to this block with the given
            //	variable assignments.
            $str[$blocks[$blockcount]/* . '.'*/][] = $vararray;
        } else {
            // Top-level block.
            // Add a new iteration to this block with the variable assignments
            // we were given.
            $this->data[$blockname/* . '.'*/][] = $vararray;
        }

        return true;
    }

    /**
     * @param array $template
     * @deprecated
     */
    public function set_filenames(array $template)
    {   $this->templates = array_merge($this->templates, $template);
    }

    /**
     * @param $vars
     * @deprecated
     */
    public function pparse($templateName)
    {
        $this->engine->display($this->templates[$templateName], $this->data);
    }
}
