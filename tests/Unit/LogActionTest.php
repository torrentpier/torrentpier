<?php

use TorrentPier\Legacy\LogAction;

describe('LogAction', function () {
    it('can be instantiated', function () {
        $instance = new LogAction();

        expect($instance)->toBeInstanceOf(LogAction::class);
    });

    it('has log_type property with action types', function () {
        $instance = new LogAction();

        expect($instance->log_type)->toBeArray()
            ->and($instance->log_type)->toHaveKey('mod_topic_delete')
            ->and($instance->log_type)->toHaveKey('mod_post_delete')
            ->and($instance->log_type)->toHaveKey('adm_user_ban');
    });

});
