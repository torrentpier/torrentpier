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
                            $cur_files_ary[] = "$name <i>$length</i>";
                        }
                    }
                    asort($cur_files_ary);
                } else {
                    $name = $f['path'][0];
                    $this->files_ary['/'][] = "$name <i>$length</i>";
                    natsort($this->files_ary['/']);
                }
            }
        } else {
            $name = clean_tor_dirname($info['name']);
            $length = (float)$info['length'];
            $this->files_ary['/'][] = "$name <i>$length</i>";
            natsort($this->files_ary['/']);
        }
    }

    /**
     * File list generation for v2 supported torrents
     *
     * @param array $array
     * @param string $name
     * @return string
     */
    public function fileTreeList(array $array, string $name = ''): string
    {
        $folders = [];
        $rootFiles = [];

        foreach ($array as $key => $value) {
            $key = htmlCHR($key);
            if (!isset($value[''])) {
                $html_v2 = $this->fileTreeList($value);
                $folders[] = "<li><span class=\"b\">$key</span><ul>$html_v2</ul></li>";
            } else {
                $length = (int)$value['']['length'];
                $root = bin2hex($value['']['pieces root'] ?? '');
                $rootFiles[] = "<li><span>$key<i>$length</i><p>$root</p></span></li>";
            }
        }

        $allFiles = implode('', [...$folders, ...$rootFiles]);

        return '<div class="tor-root-dir">' . (empty($folders) ? '' : htmlCHR($name)) . '</div><ul class="tree-root">' . $allFiles . '</ul>';
    }

    /**
     * Table generation for BitTorrent v2 compatible torrents
     *
     * @param array $array
     * @param string $parent
     * @return array
     */
    public function fileTreeTable(array $array, string $parent = ''): array
    {
        $filesList = [];

        foreach ($array as $key => $value) {
            $key = htmlCHR($key);
            $current = "$parent/$key";
            if (!isset($value[''])) {
                $this->fileTreeTable($value, $current);
            } else {
                $length = (int)$value['']['length'];
                $root = bin2hex($value['']['pieces root'] ?? '');
                $filesList[] = '<tr><td>' . $current . '</td><td>' . humn_size($length, 2) . '</td><td>' . $root . '</td></tr><tr>';
            }
        }

        return $filesList;
    }
}
