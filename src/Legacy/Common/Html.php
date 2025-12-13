<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Common;

/**
 * Class Html
 * @package TorrentPier\Legacy\Common
 */
class Html
{
    public $options = '';
    public $out = '';
    public $attr = [];
    public $cur_attr;
    public $max_length = HTML_SELECT_MAX_LENGTH;
    public $selected = [];

    /**
     * @param $name
     * @param $params
     * @param null $selected
     * @param int $max_length
     * @param null $multiple_size
     * @param string $js
     * @return string
     */
    public function build_select($name, $params, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '')
    {
        if (empty($params)) {
            return '';
        }

        $this->options = '';
        $this->selected = array_flip((array)$selected);
        $this->max_length = $max_length;

        $this->attr = [];
        $this->cur_attr = &$this->attr;

        if (isset($params['__attributes'])) {
            $this->attr = $params['__attributes'];
            unset($params['__attributes']);
        }

        $this->_build_select_rec($params);

        $select_params = $js ? " {$js}" : '';
        $select_params .= ' autocomplete="off"';
        $select_params .= $multiple_size ? ' multiple size="' . $multiple_size . '"' : '';
        $select_params .= ' name="' . htmlCHR($name) . '"';
        $select_params .= ' id="' . htmlCHR($name) . '"';

        return "\n<select {$select_params}>\n" . $this->options . "</select>\n";
    }

    /**
     * @param $params
     */
    public function _build_select_rec($params)
    {
        foreach ($params as $opt_name => $opt_val) {
            $opt_name = rtrim($opt_name);

            if (\is_array($opt_val)) {
                $this->cur_attr = &$this->cur_attr[$opt_name];

                $label = str_short(htmlCHR($opt_name), $this->max_length);

                $this->options .= "\t<optgroup label=\"&nbsp;" . $label . "\">\n";
                $this->_build_select_rec($opt_val);
                $this->options .= "\t</optgroup>\n";

                $this->cur_attr = &$this->attr;
            } else {
                $text = str_short(htmlCHR($opt_name), $this->max_length);
                $value = ' value="' . htmlCHR($opt_val) . '"';

                $class = isset($this->cur_attr[$opt_name]['class']) ? ' class="' . $this->cur_attr[$opt_name]['class'] . '"' : '';
                $style = isset($this->cur_attr[$opt_name]['style']) ? ' style="' . $this->cur_attr[$opt_name]['style'] . '"' : '';

                $selected = isset($this->selected[$opt_val]) ? HTML_SELECTED : '';
                $disabled = isset($this->cur_attr[$opt_name]['disabled']) ? HTML_DISABLED : '';

                $this->options .= "\t\t<option" . $class . $style . $selected . $disabled . $value . '>&nbsp;' . $text . "&nbsp;</option>\n";
            }
        }
    }

    /**
     * @param $array
     * @param string $ul
     * @param string $li
     * @return string
     */
    public function array2html($array, $ul = 'ul', $li = 'li')
    {
        $this->out = '';
        $this->_array2html_rec($array, $ul, $li);

        return "<{$ul} class=\"tree-root\">{$this->out}</{$ul}>";
    }

    /**
     * @param $array
     * @param $ul
     * @param $li
     */
    public function _array2html_rec($array, $ul, $li)
    {
        foreach ($array as $k => $v) {
            if (\is_array($v)) {
                $this->out .= "<{$li}><span class=\"b\">{$k}</span><{$ul}>";
                $this->_array2html_rec($v, $ul, $li);
                $this->out .= "</{$ul}></{$li}>";
            } else {
                $this->out .= "<{$li}><span>{$v}</span></{$li}>";
            }
        }
    }

    /**
     * All arguments should be already htmlspecialchar (if needed)
     *
     * @param $name
     * @param $title
     * @param bool $checked
     * @param bool $disabled
     * @param null $class
     * @param null $id
     * @param int $value
     * @return string
     */
    public function build_checkbox($name, $title, $checked = false, $disabled = false, $class = null, $id = null, $value = 1)
    {
        $name = ' name="' . $name . '" ';
        $value = ' value="' . $value . '" ';
        $title = $class ? '<span class="' . $class . '">' . $title . '</span>' : $title;
        $id = $id ? " id=\"{$id}\" " : '';
        $checked = $checked ? HTML_CHECKED : '';
        $disabled = $disabled ? HTML_DISABLED : '';

        return '<label><input type="checkbox" ' . $id . $name . $value . $checked . $disabled . ' />&nbsp;' . $title . '&nbsp;</label>';
    }
}
