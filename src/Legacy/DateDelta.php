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
 * Class DateDelta
 * @package TorrentPier\Legacy
 */
class DateDelta
{
    public $auto_granularity = [
        60 => 'seconds', // set granularity to "seconds" if delta less then 1 minute
        10800 => 'minutes', // 3 hours
        259200 => 'hours', // 3 days
        31363200 => 'mday', // 12 months
        311040000 => 'mon', // 10 years
    ];
    public $intervals = [];
    public $format = '';

    public function __construct()
    {
        global $lang;

        $this->intervals = $lang['DELTA_TIME']['INTERVALS'];
        $this->format = $lang['DELTA_TIME']['FORMAT'];
    }

    /**
     * Makes the spellable phrase
     *
     * @param $first
     * @param $last
     * @param string $from
     * @return bool|string
     */
    public function spellDelta($first, $last, $from = 'auto')
    {
        if ($last < $first) {
            $old_first = $first;
            $first = $last;
            $last = $old_first;
        }

        if ($from == 'auto') {
            $from = 'year';
            $diff = $last - $first;
            foreach ($this->auto_granularity as $seconds_count => $granule) {
                if ($diff < $seconds_count) {
                    $from = $granule;
                    break;
                }
            }
        }

        // Solve data delta.
        $delta = $this->getDelta($first, $last);
        if (!$delta) {
            return false;
        }

        // Make spellable phrase.
        $parts = [];
        $intervals = $GLOBALS['lang']['DELTA_TIME']['INTERVALS'];

        foreach (array_reverse($delta) as $k => $n) {
            if (!$n) {
                if ($k == $from) {
                    if (!$parts) {
                        $parts[] = declension($n, $this->intervals[$k], $this->format);
                    }
                    break;
                }
                continue;
            }
            $parts[] = declension($n, $this->intervals[$k], $this->format);
            if ($k == $from) {
                break;
            }
        }
        return implode(' ', $parts);
    }

    /**
     * Returns the associative array with date deltas
     *
     * @param $first
     * @param $last
     * @return bool
     */
    public function getDelta($first, $last)
    {
        if ($last < $first) {
            return false;
        }

        // Solve H:M:S part.
        $hms = ($last - $first) % (3600 * 24);
        $delta['seconds'] = $hms % 60;
        $delta['minutes'] = floor($hms / 60) % 60;
        $delta['hours'] = floor($hms / 3600) % 60;

        // Now work only with date, delta time = 0.
        $last -= $hms;
        $f = getdate($first);
        $l = getdate($last); // the same daytime as $first!

        $dYear = $dMon = $dDay = 0;

        // Delta day. Is negative, month overlapping.
        $dDay += $l['mday'] - $f['mday'];
        if ($dDay < 0) {
            $monlen = $this->monthLength(date('Y', $first), date('m', $first));
            $dDay += $monlen;
            $dMon--;
        }
        $delta['mday'] = $dDay;

        // Delta month. If negative, year overlapping.
        $dMon += $l['mon'] - $f['mon'];
        if ($dMon < 0) {
            $dMon += 12;
            $dYear--;
        }
        $delta['mon'] = $dMon;

        // Delta year.
        $dYear += $l['year'] - $f['year'];
        $delta['year'] = $dYear;

        return $delta;
    }

    /**
     * Returns the length (in days) of the specified month
     *
     * @param $year
     * @param $mon
     * @return int
     */
    public function monthLength($year, $mon)
    {
        $l = 28;
        while (checkdate($mon, $l + 1, $year)) {
            $l++;
        }
        return $l;
    }
}
