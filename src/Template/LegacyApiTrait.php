<?php

namespace TorrentPier\Template;

use function count;
use function explode;
use function strpos;

trait LegacyApiTrait
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $vars
     * @deprecated
     */
    public function assign_vars(array $vars): void
    {
        foreach ($vars as $key => $val) {
            $this->assign_var($key, $val);
        }
    }

    /**
     * @param string $key
     * @param mixed  $val
     * @deprecated
     */
    public function assign_var(string $key, $val = true): void
    {
        $this->data[$key] = $val;
    }

    /**
     * @param string $blockName
     * @param mixed $varArray
     * @deprecated
     */
    public function assign_block_vars(string $blockName, $varArray): void
    {
        if (false !== strpos($blockName, '.')) {
            // Nested block.
            $blocks = explode('.', $blockName);
            $blockCount = count($blocks) - 1;

            $str = &$this->data;
            for ($i = 0; $i < $blockCount; $i++) {
                $str = &$str[$blocks[$i]];
                $str = &$str[count($str) - 1];
            }
            $str[$blocks[$blockCount]][] = $varArray;
        } else {
            $this->data[$blockName][] = $varArray;
        }
    }

    /**
     * @param array|string[] $templates
     * @deprecated
     */
    public function set_filenames(array $templates): void
    {
        foreach ($templates as $name => $path) {
            $this->addTemplate($name, $path);
        }
    }

    /**
     * @param string $templateName
     * @deprecated
     */
    public function pparse(string $templateName): void
    {
        $this->display($templateName, $this->data);
    }
}
