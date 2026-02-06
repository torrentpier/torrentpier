<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Data;

/**
 * Torrent client PeerID mappings.
 */
final class TorrentClients
{
    public const array PEER_IDS = [
        '-AG' => 'Ares',
        '-AZ' => 'Vuze',
        '-A~' => 'Ares',
        '-BC' => 'BitComet',
        '-BE' => 'BitTorrent SDK',
        '-BI' => 'BiglyBT',
        '-BL' => 'BitLord',
        '-BT' => 'BitTorrent',
        '-CT' => 'CTorrent',
        '-DE' => 'Deluge',
        '-FD' => 'Free Download Manager',
        'FD6' => 'Free Download Manager',
        '-FG' => 'FlashGet',
        '-FL' => 'Folx',
        '-HL' => 'Halite',
        '-KG' => 'KGet',
        '-KT' => 'KTorrent',
        '-LT' => 'libTorrent',
        '-Lr' => 'LibreTorrent',
        '-TR' => 'Transmission',
        '-tT' => 'tTorrent',
        '-UM' => 'uTorrent Mac',
        '-UT' => 'uTorrent',
        '-UW' => 'uTorrent Web',
        '-WW' => 'WebTorrent',
        '-WD' => 'WebTorrent',
        '-XL' => 'Xunlei',
        '-PI' => 'PicoTorrent',
        '-qB' => 'qBittorrent',
        '-MG' => 'MediaGet',
        'M' => 'BitTorrent',
        'MG' => 'MediaGet',
        'OP' => 'Opera',
        'TIX' => 'Tixati',
        'aria2-' => 'Aria2',
        'A2' => 'Aria2',
    ];

    /**
     * Get all client mappings.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::PEER_IDS;
    }

    /**
     * Get client name by peer ID prefix.
     */
    public static function getName(string $peerId): ?string
    {
        $prefixes = array_keys(self::PEER_IDS);
        usort($prefixes, fn($a, $b) => \strlen($b) - \strlen($a));

        foreach ($prefixes as $prefix) {
            if (str_starts_with($peerId, $prefix)) {
                return self::PEER_IDS[$prefix];
            }
        }

        return null;
    }
}
