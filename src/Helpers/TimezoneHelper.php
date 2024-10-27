<?php

namespace TorrentPier\Helpers;

final class TimezoneHelper
{
    /**
     * Validate timezone
     *
     * @param string $timezone
     * @return string
     */
    public static function validate(string $timezone): string
    {
        global $bb_cfg;

        if (preg_match('/^(?:[+-]?(?:[0-9]{2}:[0-9]{2}|[0-9]{1,2}))$/', $timezone)) {
            return $timezone;
        }

        return $bb_cfg['board_timezone'];
    }
}
