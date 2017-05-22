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
 * Reserved prefixes:
 *
 *  "L_" - lang var, {L_VAR} is eq to $lang['VAR']
 *  "$"  - php var,  {$VAR}  is eq to $VAR (in $this->execute() scope!)
 *  "#"  - constant, {#CON}  is eq to CON
 *
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

define('XS_SEPARATOR', '.');
define('XS_USE_ISSET', '1');

// cache filenames prefix
define('XS_TPL_PREFIX', 'tpl_');
define('XS_TPL_PREFIX2', 'tpl2_');

// internal xs mod definitions. do not edit.
define('XS_TAG_NONE', 0);
define('XS_TAG_PHP', 1);
define('XS_TAG_BEGIN', 2);
define('XS_TAG_END', 3);
define('XS_TAG_INCLUDE', 4);
define('XS_TAG_IF', 5);
define('XS_TAG_ELSE', 6);
define('XS_TAG_ELSEIF', 7);
define('XS_TAG_ENDIF', 8);
define('XS_TAG_BEGINELSE', 11);
