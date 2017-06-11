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

namespace TorrentPier\Db;

class LogProcessor
{
    public $timeQueries = 0;
    public $timePrepares = 0;
    public $numQueries = 0;
    public $numPrepares = 0;

    public function __invoke(array $record)
    {
        $isPrepare = isset($record['context']['prepare']);
        if ($isPrepare) {
            $record['message'] = "Prepare {$record['message']}";
            $this->numPrepares++;
        } else {
            $this->numQueries++;
        }
        if (isset($record['context']['time'])) {
            $t = $record['context']['time'];
            $record['message'] = '[' . sprintf('%.5f', $t) . '] ' . $record['message'];
            if ($isPrepare) {
                $this->timePrepares += $t;
            } else {
                $this->timeQueries += $t;
            }
        }
        return $record;
    }
}
