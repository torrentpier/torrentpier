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

use \PDOStatement;
use \PDOException;
use TorrentPier\Db;

class Statement extends PDOStatement
{
    protected $db = null;

    protected function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function execute(/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
        $input = null)
    {
        try {
            if (isset($this->db->logger) && strncasecmp($this->queryString, 'EXPLAIN', 7)) {
                $t = microtime(true);
            }
            return parent::execute($input);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new IntegrityViolationException($e);
            }
            throw new Exception($e);
        } finally {
            if (isset($t)) {
                $context = ['time' => microtime(true) - $t, 'input' => $input];
                if ($this->db->isLogExplain && preg_match('#^\\s*SELECT\\s#i', $this->queryString)) {
                    $context['explain'] = $this->db->explain($this->queryString, $input);
                }
                $this->db->logger->debug($this->queryString, $context);
            }
        }
    }
}
