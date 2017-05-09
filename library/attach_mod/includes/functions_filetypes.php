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
        return array(
            $width,
            $height,
            6
        );
    }

    $error = false;
    fclose($fp);

    // GIF - IMAGE

    $fp = @fopen($file, 'rb');

    $tmp_str = fread($fp, 3);

    if ($tmp_str == 'GIF') {
        $tmp_str = fread($fp, 3);
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
        return array(
            $width,
            $height,
            1
        );
    }

    $error = false;
    fclose($fp);

    // JPG - IMAGE
    $fp = @fopen($file, 'rb');

    $tmp_str = fread($fp, 4);
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
                $str = fread($fp, 2);
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
        return array(
            $width,
            $height,
            2
        );
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
            $tmp_str = fread($fp, 52);

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
        return array(
            $width,
            $height,
            7
        );
    }

    fclose($fp);

    return $size;
}
