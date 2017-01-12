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

// Tracker type
define('TR_TYPE', 'yse'); // 'sky' (SkyTracker) or 'yse' (TBDev YSE)
// Options
define('CLEAN', true); // Clean TorrentPier's database before converting?
//Users
define('CONVERT_USERS', true); // Converting users is enabled?
define('C_USERS_PER_ONCE', 250); // Number of users converting per once
//Torrents and categories
define('CONVERT_TORRENTS', true); // Converting torrents and categories is enabled?
define('C_TORRENTS_PER_ONCE', 400); // Number of torrents converting per once
define('BDECODE', false); // Recalculate info_hash using bdecode?
//Comments
define('CONVERT_COMMENTS', true); // Converting comments is enabled?
define('C_COMMENTS_PER_ONCE', 400); // Number of comments converting per once
//Mybb forums & topics
define('CONVERT_MYBB_FORUMS', false); // Converting forums is enabled?
define('C_FORUMS_PER_ONCE', 100); // Number of forums converting per once
