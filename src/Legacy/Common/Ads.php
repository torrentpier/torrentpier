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

namespace TorrentPier\Legacy\Common;

/**
 * Class Ads
 * @package TorrentPier\Legacy\Common
 */
class Ads
{
    public $ad_blocks = [];
    public $active_ads = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        global $bb_cfg;

        $this->ad_blocks =& $bb_cfg['ad_blocks'];
        $this->active_ads = !empty($bb_cfg['active_ads']) ? @unserialize($bb_cfg['active_ads']) : [];
    }

    /**
     * Get ads to show for each block
     *
     * @param $block_types
     * @return array
     */
    public function get($block_types)
    {
        $ads = [];

        if ($this->active_ads) {
            $block_ids = $this->get_block_ids($block_types);

            if ($ad_ids = $this->get_ad_ids($block_ids)) {
                $ad_html = $this->get_ads_html();

                foreach ($ad_ids as $block_id => $ad_id) {
                    $ads[$block_id] =& $ad_html[$ad_id];
                }
            }
        }

        return $ads;
    }

    /**
     * Get ads html
     *
     * @return string
     */
    public function get_ads_html()
    {
        global $datastore;
        if (!$ads_html = $datastore->get('ads')) {
            $datastore->update('ads');
            $ads_html = $datastore->get('ads');
        }

        return $ads_html;
    }

    /**
     * Get block_ids for specified block_types
     *
     * @param $block_types
     * @return array
     */
    public function get_block_ids($block_types)
    {
        $block_ids = [];

        foreach ($block_types as $block_type) {
            if ($blocks =& $this->ad_blocks[$block_type]) {
                $block_ids = array_merge($block_ids, array_keys($blocks));
            }
        }

        return $block_ids;
    }

    /**
     * Get ad_ids for specified blocks
     *
     * @param $block_ids
     * @return array
     */
    public function get_ad_ids($block_ids)
    {
        $ad_ids = [];

        foreach ($block_ids as $block_id) {
            if ($ads =& $this->active_ads[$block_id]) {
                shuffle($ads);
                $ad_ids[$block_id] = $ads[0];
            }
        }

        return $ad_ids;
    }
}
