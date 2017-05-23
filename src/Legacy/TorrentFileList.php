<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TorrentPier\Legacy;

/**
 * Class TorrentFileList
 * @package TorrentPier\Legacy
 */
class TorrentFileList
{
    public $tor_decoded = [];
    public $files_ary = [
        '/' => []
    ];
    public $multiple = false;
    public $root_dir = '';
    public $files_html = '';

    public function __construct($decoded_file_contents)
    {
        $this->tor_decoded = $decoded_file_contents;
    }

    /**
     * Получение списка файлов
     *
     * @return string
     */
    public function get_filelist()
    {
        global $html;

        $this->build_filelist_array();

        if ($this->multiple) {
            if ($this->files_ary['/'] !== '') {
                $this->files_ary = array_merge($this->files_ary, $this->files_ary['/']);
                unset($this->files_ary['/']);
            }
            $filelist = $html->array2html($this->files_ary);
            return "<div class=\"tor-root-dir\">{$this->root_dir}</div>$filelist";
        }

        return implode('', $this->files_ary['/']);
    }

    /**
     * Формирование списка файлов
     */
    private function build_filelist_array()
    {
        $info = $this->tor_decoded['info'];

        if (isset($info['name.utf-8'])) {
            $info['name'] =& $info['name.utf-8'];
        }

        if (isset($info['files']) && is_array($info['files'])) {
            $this->root_dir = isset($info['name']) ? '../' . clean_tor_dirname($info['name']) : '...';
            $this->multiple = true;

            foreach ($info['files'] as $f) {
                if (isset($f['path.utf-8'])) {
                    $f['path'] =& $f['path.utf-8'];
                }
                if (!isset($f['path']) || !is_array($f['path'])) {
                    continue;
                }
                array_deep($f['path'], 'clean_tor_dirname');

                $length = isset($f['length']) ? (float)$f['length'] : 0;
                $subdir_count = count($f['path']) - 1;

                if ($subdir_count > 0) {
                    $name = array_pop($f['path']);
                    $cur_files_ary =& $this->files_ary;

                    for ($i = 0, $j = 1; $i < $subdir_count; $i++, $j++) {
                        $subdir = $f['path'][$i];

                        if (!isset($cur_files_ary[$subdir])) {
                            $cur_files_ary[$subdir] = array();
                        }
                        $cur_files_ary =& $cur_files_ary[$subdir];

                        if ($j === $subdir_count) {
                            if (is_string($cur_files_ary)) {
                                $GLOBALS['bnc_error'] = 1;
                                break;
                            }
                            $cur_files_ary[] = $this->build_file_item($name, $length);
                        }
                    }
                    natsort($cur_files_ary);
                } else {
                    $name = $f['path'][0];
                    $this->files_ary['/'][] = $this->build_file_item($name, $length);
                    natsort($this->files_ary['/']);
                }
            }
        } else {
            $name = clean_tor_dirname($info['name']);
            $length = (float)$info['length'];
            $this->files_ary['/'][] = $this->build_file_item($name, $length);
            natsort($this->files_ary['/']);
        }
    }

    /**
     * Формирование файла
     *
     * @param $name
     * @param $length
     * @return string
     */
    private function build_file_item($name, $length)
    {
        global $bb_cfg, $images, $lang;

        $magnet_name = $magnet_ext = '';

        if ($bb_cfg['magnet_links_enabled']) {
            $magnet_name = '<a title="' . $lang['DC_MAGNET'] . '" href="dchub:magnet:?kt=' . $name . '&xl=' . $length . '"><img src="' . $images['icon_dc_magnet'] . '" width="10" height="10" border="0" /></a>';
            $magnet_ext = '<a title="' . $lang['DC_MAGNET_EXT'] . '" href="dchub:magnet:?kt=.' . substr(strrchr($name, '.'), 1) . '&xl=' . $length . '"><img src="' . $images['icon_dc_magnet_ext'] . '" width="10" height="10" border="0" /></a>';
        }

        return "$name <i>$length</i> $magnet_name $magnet_ext";
    }
}
