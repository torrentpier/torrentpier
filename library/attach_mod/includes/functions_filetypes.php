<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * All Attachment Functions needed to determine Special Files/Dimensions
 */

/**
 * Read Long Int (4 Bytes) from File
 */
function read_longint($fp)
{
    $data = fread($fp, 4);

    $value = ord($data[0]) + (ord($data[1]) << 8) + (ord($data[2]) << 16) + (ord($data[3]) << 24);
    if ($value >= 4294967294) {
        $value -= 4294967296;
    }

    return $value;
}

/**
 * Read Word (2 Bytes) from File - Note: It's an Intel Word
 */
function read_word($fp)
{
    $data = fread($fp, 2);

    return ord($data[1]) * 256 + ord($data[0]);
}

/**
 * Read Byte
 */
function read_byte($fp)
{
    $data = fread($fp, 1);

    return ord($data);
}

/**
 * Get Image Dimensions
 */
function image_getdimension($file)
{
    $xmax = null;
    $xmin = null;
    $ymax = null;
    $ymin = null;
    $size = @getimagesize($file);

    if ($size[0] != 0 || $size[1] != 0) {
        return $size;
    }

    // Try to get the Dimension manually, depending on the mimetype
    $fp = @fopen($file, 'rb');
    if (!$fp) {
        return $size;
    }

    $error = false;

    // BMP - IMAGE
    $tmp_str = fread($fp, 2);
    if ($tmp_str == 'BM') {
        $length = read_longint($fp);

        if ($length <= 6) {
            $error = true;
        }

        if (!$error) {
            $i = read_longint($fp);
            if ($i != 0) {
                $error = true;
            }
        }

        if (!$error) {
            $i = read_longint($fp);

            if ($i != 0x3E && $i != 0x76 && $i != 0x436 && $i != 0x36) {
                $error = true;
            }
        }

        if (!$error) {
            $tmp_str = fread($fp, 4);
            $width = read_longint($fp);
            $height = read_longint($fp);

            if ($width > 3000 || $height > 3000) {
                $error = true;
            }
        }
    } else {
        $error = true;
    }

    if (!$error) {
        fclose($fp);
        return [
            $width,
            $height,
            6
        ];
    }

    $error = false;
    fclose($fp);

    // GIF - IMAGE
    $fp = @fopen($file, 'rb');
    $tmp_str = fread($fp, 3);

    if ($tmp_str == 'GIF') {
        $width = read_word($fp);
        $height = read_word($fp);

        $info_byte = fread($fp, 1);
        $info_byte = ord($info_byte);
        if (($info_byte & 0x80) != 0x80 && ($info_byte & 0x80) != 0) {
            $error = true;
        }

        if (!$error) {
            if (($info_byte & 8) != 0) {
                $error = true;
            }
        }
    } else {
        $error = true;
    }

    if (!$error) {
        fclose($fp);
        return [
            $width,
            $height,
            1
        ];
    }

    $error = false;
    fclose($fp);

    // JPG - IMAGE
    $fp = @fopen($file, 'rb');
    $w1 = read_word($fp);

    if ((int)$w1 < 16) {
        $error = true;
    }

    if (!$error) {
        $tmp_str = fread($fp, 4);
        if ($tmp_str == 'JFIF') {
            $o_byte = fread($fp, 1);
            if ((int)$o_byte != 0) {
                $error = true;
            }

            if (!$error) {
                $b = read_byte($fp);

                if ($b != 0 && $b != 1 && $b != 2) {
                    $error = true;
                }
            }

            if (!$error) {
                $width = read_word($fp);
                $height = read_word($fp);

                if ($width <= 0 || $height <= 0) {
                    $error = true;
                }
            }
        }
    } else {
        $error = true;
    }

    if (!$error) {
        fclose($fp);
        return [
            $width,
            $height,
            2
        ];
    }

    $error = false;
    fclose($fp);

    // PCX - IMAGE
    $fp = @fopen($file, 'rb');
    $tmp_str = fread($fp, 3);

    if ((ord($tmp_str[0]) == 10) && (ord($tmp_str[1]) == 0 || ord($tmp_str[1]) == 2 || ord($tmp_str[1]) == 3 || ord($tmp_str[1]) == 4 || ord($tmp_str[1]) == 5) && (ord($tmp_str[2]) == 1)) {
        $b = fread($fp, 1);

        if (ord($b) != 1 && ord($b) != 2 && ord($b) != 4 && ord($b) != 8 && ord($b) != 24) {
            $error = true;
        }

        if (!$error) {
            $xmin = read_word($fp);
            $ymin = read_word($fp);
            $xmax = read_word($fp);
            $ymax = read_word($fp);

            $b = fread($fp, 1);
            if ($b != 0) {
                $error = true;
            }
        }

        if (!$error) {
            $width = $xmax - $xmin + 1;
            $height = $ymax - $ymin + 1;
        }
    } else {
        $error = true;
    }

    if (!$error) {
        fclose($fp);
        return [
            $width,
            $height,
            7
        ];
    }

    fclose($fp);

    return $size;
}
