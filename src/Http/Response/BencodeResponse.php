<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Http\Response;

use Arokettu\Bencode\Bencode;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;

/**
 * Bencode Response for BitTorrent Tracker
 *
 * PSR-7 response that encodes data using Bencode format.
 * Used for BitTorrent protocol responses (announce, scrape).
 */
class BencodeResponse extends Response
{
    /**
     * Create a new Bencode response
     *
     * @param array $data Data to encode as Bencode
     * @param int $status HTTP status code (default 200)
     * @param array $headers Additional headers
     */
    public function __construct(array $data, int $status = 200, array $headers = [])
    {
        $body = Bencode::encode($data);

        $stream = new Stream('php://temp', 'wb+');
        $stream->write($body);
        $stream->rewind();

        // Merge default headers with provided headers
        $defaultHeaders = [
            'Content-Type' => 'text/plain',
            'Pragma' => 'no-cache',
            'Content-Length' => (string)\strlen($body),
        ];

        parent::__construct($stream, $status, array_merge($defaultHeaders, $headers));
    }
}
