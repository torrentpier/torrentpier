<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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

    /**
     * Constructor
     *
     * @param $decoded_file_contents
     */
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
     *
     * @return void
     */
    private function build_filelist_array()
    {
        $info = $this->tor_decoded['info'];

        if (isset($info['name.utf-8'])) {
            $info['name'] =& $info['name.utf-8'];
        }

        if (isset($info['files']) && \is_array($info['files'])) {
            $this->root_dir = isset($info['name']) ? '../' . clean_tor_dirname($info['name']) : '...';
            $this->multiple = true;

            foreach ($info['files'] as $f) {
                if (isset($f['path.utf-8'])) {
                    $f['path'] =& $f['path.utf-8'];
                }
                if (!isset($f['path']) || !\is_array($f['path'])) {
                    continue;
                }
                // Exclude padding files
                if (($f['attr'] ?? null) === 'p') {
                    continue;
                }
                array_deep($f['path'], 'clean_tor_dirname');

                $length = isset($f['length']) ? (float)$f['length'] : 0;
                $subdir_count = \count($f['path']) - 1;

                if ($subdir_count > 0) {
                    $name = array_pop($f['path']);
                    $cur_files_ary =& $this->files_ary;

                    for ($i = 0, $j = 1; $i < $subdir_count; $i++, $j++) {
                        $subdir = $f['path'][$i];

                        if (!isset($cur_files_ary[$subdir])) {
                            $cur_files_ary[$subdir] = [];
                        }
                        $cur_files_ary =& $cur_files_ary[$subdir];

                        if ($j === $subdir_count) {
                            if (\is_string($cur_files_ary)) {
                                $GLOBALS['bnc_error'] = 1;
                                break;
                            }
                            $cur_files_ary[] = $this->build_file_item($name, $length);
                        }
                    }
                    asort($cur_files_ary);
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
    private function build_file_item($name, $length): string
    {
        global $bb_cfg, $images, $lang;

        $magnet_name = $magnet_ext = '';

        if ($bb_cfg['magnet_links_enabled']) {
            $magnet_name = '<a title="' . $lang['DC_MAGNET'] . '" href="dchub:magnet:?kt=' . $name . '&xl=' . $length . '"><img src="' . $images['icon_dc_magnet'] . '" width="10" height="10" border="0" /></a>';
            $magnet_ext = '<a title="' . $lang['DC_MAGNET_EXT'] . '" href="dchub:magnet:?kt=.' . substr(strrchr($name, '.'), 1) . '&xl=' . $length . '"><img src="' . $images['icon_dc_magnet_ext'] . '" width="10" height="10" border="0" /></a>';
        }

        return "$name <i>$length</i> $magnet_name $magnet_ext";
    }

	public function fileTree($array, $name = '')
	{
		$folders = [];
		$rootFiles = [];

		foreach ($array as $key => $value) {
			$key = htmlCHR($key);
			if (!isset($value[''])) {
				$html_v2 = $this->fileTree($value);
				$folders[] = "<li><span class=\"b\">$key</span><ul>$html_v2</ul></li>";
			} else {
				$length = (int)$value['']['length'];
				$root = bin2hex($value['']['pieces root'] ?? '');
				$rootFiles[] = "<li><span>$key<i>$length</i> <p>$root</p></span></li>";
			}
		}

		$allFiles = [...$folders, ...$rootFiles];

		return '<div class="tor-root-dir">' . ((empty($folders) || empty($name)) ? '' : htmlCHR($name)) . '</div><ul class="tree-root">' . implode('', $allFiles) . '</ul>';
	}
}