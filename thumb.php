<?php

// �������� ����������� � ����������� � �������
// ����� _Xz_ (http://torrentpier.me/members/_xz_.2/)

define('IN_PHPBB', true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');

$user->session_start();

$topic_id = (int) request_var('t', '0');
if (!$topic_id) {
    message_die(GENERAL_MESSAGE, 'NO_TOPIC_ID');
}

$row = DB()->fetch_row("SELECT pt.post_text
    FROM ". BB_BT_TORRENTS ." tr
        LEFT JOIN ". BB_TOPICS ." t ON(tr.topic_id = t.topic_id)
        LEFT JOIN ". BB_POSTS_TEXT ." pt ON(pt.post_id = tr.post_id)
    WHERE t.topic_id  = $topic_id");

if (!$row) {
    message_die(GENERAL_MESSAGE, 'NO_TOPIC');
}

preg_match_all('/\[poster\](.*?)\[\/poster\]/i', $row['post_text'], $poster0, PREG_SET_ORDER);
preg_match_all('/\[img=right\](.*?)\[\/img\]/i', $row['post_text'], $poster, PREG_SET_ORDER);
preg_match_all('/\[img=left\](.*?)\[\/img\]/i', $row['post_text'], $poster2, PREG_SET_ORDER);
preg_match_all('/\[img\](.*?)\[\/img\]/i', $row['post_text'], $poster3, PREG_SET_ORDER);

$url = '';
if (isset($poster[0][1])) {
    $url = $poster[0][1];
} elseif (isset($poster0[0][1])) {
    $url = $poster0[0][1];
} elseif (isset($poster2[0][1])) {
    $url = $poster2[0][1];
} elseif (isset($poster3[0][1])) {
    $url = $poster3[0][1];
}

$filetype = substr(strrchr($url, '.'), 1);
$filename = substr($url, strrpos($url, '/'));

$folder = 'internal_data/thumbnails'; // ����� ���� ���������
$thumb_file = $folder . '/' . $filename;

// ������� ������� ���� ��� ������
if (@fopen($url, "r")) {
    // ������� ��������� ���� ��� ��������� ImageMagick
    $temp_file = tempnam(sys_get_temp_dir(), 'thumb_img_');
    file_put_contents($temp_file, file_get_contents($url));

    // ��������� ��������� ������� � ������� ImageMagick
    $command = "convert $temp_file +profile '*' $temp_file";
    shell_exec($command);

    // ��������� ����������� � ������� Imagick
    $imagick = new Imagick($temp_file);

    // �������� ���������� �����
    unlink($temp_file);

    $max_width = 110;
    $max_height = 135;

    // �������� ������ ����������� � ����������� ���������
    $imagick->resizeImage($max_width, $max_height, Imagick::FILTER_LANCZOS, 1);

    // ��������� ��� ������ �����������
    header('Content-type: image/' . $filetype);
    header('Content-Disposition: filename='. $filename);

    // ����� �����������
    echo $imagick;

    // ��������� �����������
    $imagick->clear();
    $imagick->destroy();

    exit;
} else {
    header('Content-type: image/png');
    header('Content-Disposition: filename=noposter.png');
    readfile($no_poster);
}
?>
