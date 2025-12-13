<?php

use TorrentPier\Legacy\Common\Html;

// Define constant if not exists (used by Html class)
if (!defined('HTML_SELECT_MAX_LENGTH')) {
    define('HTML_SELECT_MAX_LENGTH', 60);
}

describe('Html', function () {
    it('can be instantiated', function () {
        $instance = new Html;

        expect($instance)->toBeInstanceOf(Html::class);
    });

    it('has default properties', function () {
        $instance = new Html;

        expect($instance->options)->toBe('')
            ->and($instance->out)->toBe('')
            ->and($instance->attr)->toBeArray()
            ->and($instance->max_length)->toBe(HTML_SELECT_MAX_LENGTH);
    });
});
