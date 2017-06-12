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

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $lang;

if (!isset($this->request['attach_id'])) {
    $this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
$attach_id = (int)$this->request['attach_id'];

$torrent = DB()->fetch_row("SELECT attach_id, physical_filename FROM " . BB_ATTACHMENTS_DESC . " WHERE attach_id = $attach_id LIMIT 1");
if (!$torrent) {
    $this->ajax_die($lang['ERROR_BUILD']);
}

$filename = get_attachments_dir() . '/' . $torrent['physical_filename'];
if (file_exists($filename) && !$file_contents = file_get_contents($filename)) {
    if (IS_AM) {
        $this->ajax_die($lang['ERROR_NO_ATTACHMENT'] . "\n\n" . htmlCHR($filename));
    } else {
        $this->ajax_die($lang['ERROR_NO_ATTACHMENT']);
    }
}

if (!$tor = \Rych\Bencode\Bencode::decode($file_contents)) {
    return $lang['TORFILE_INVALID'];
}

$torrent = new TorrentPier\Legacy\TorrentFileList($tor);
$tor_filelist = $torrent->get_filelist();

$this->response['html'] = $tor_filelist;
