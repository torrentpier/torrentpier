<?php

use TorrentPier\Ajax;

describe('Ajax', function () {
    it('can be instantiated', function () {
        $instance = new Ajax;

        expect($instance)->toBeInstanceOf(Ajax::class);
    });

    it('has response property initialized', function () {
        $instance = new Ajax;

        expect($instance->response)->toBeArray();
    });
});
