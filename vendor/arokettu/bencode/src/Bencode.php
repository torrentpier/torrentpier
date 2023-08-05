<?php

declare(strict_types=1);

namespace SandFox\Bencode;

/**
 * Class Bencode
 * @package SandFox\Bencode
 * @author Anton Smirnov
 * @license MIT
 */
final class Bencode
{
    /**
     * Decode bencoded data from string
     *
     * @param string $bencoded
     * @param array $options
     * @return mixed
     */
    public static function decode(string $bencoded, array $options = [])
    {
        return (new Decoder($options))->decode($bencoded);
    }

    /**
     * @param resource $readStream Read capable stream
     * @param array $options
     * @return mixed
     */
    public static function decodeStream($readStream, array $options = [])
    {
        return (new Decoder($options))->decodeStream($readStream);
    }

    /**
     * Load data from bencoded file
     *
     * @param string $filename
     * @param array $options
     * @return mixed
     */
    public static function load(string $filename, array $options = [])
    {
        return (new Decoder($options))->load($filename);
    }

    /**
     * Encode arbitrary data to bencode string
     *
     * @param mixed $data
     * @param array $options
     * @return string
     */
    public static function encode($data, array $options = []): string
    {
        return (new Encoder($options))->encode($data);
    }

    /**
     * Dump data to bencoded stream
     *
     * @param mixed $data
     * @param resource|null $writeStream Write capable stream. If null, a new php://temp will be created
     * @return resource Original or created stream
     */
    public static function encodeToStream($data, $writeStream = null, array $options = [])
    {
        return (new Encoder($options))->encodeToStream($data, $writeStream);
    }

    /**
     * Dump data to bencoded file
     *
     * @param string $filename
     * @param mixed $data
     * @param array $options
     * @return bool success of file_put_contents
     */
    public static function dump(string $filename, $data, array $options = []): bool
    {
        return (new Encoder($options))->dump($data, $filename);
    }
}
