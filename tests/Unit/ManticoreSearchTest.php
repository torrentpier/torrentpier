<?php

use TorrentPier\ManticoreSearch;

describe('ManticoreSearch', function () {
    // Note: ManticoreSearch class connects to Manticore database in constructor
    // which requires running Manticore server. This test only verifies
    // the class is autoloadable.

    it('class exists and is autoloadable', function () {
        expect(class_exists(ManticoreSearch::class))->toBeTrue();
    });

    it('has expected public methods defined', function () {
        $reflection = new ReflectionClass(ManticoreSearch::class);

        expect($reflection->hasMethod('search'))->toBeTrue()
            ->and($reflection->hasMethod('upsertTopic'))->toBeTrue()
            ->and($reflection->hasMethod('deleteTopic'))->toBeTrue();
    });
});
