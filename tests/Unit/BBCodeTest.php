<?php

use TorrentPier\Legacy\BBCode;

describe('BBCode', function () {
    // Note: BBCode class requires full application bootstrap for datastore.
    // This test only verifies the class is autoloadable.

    it('class exists and is autoloadable', function () {
        expect(class_exists(BBCode::class))->toBeTrue();
    });

    it('has expected public methods defined', function () {
        $reflection = new ReflectionClass(BBCode::class);

        expect($reflection->hasMethod('bbcode2html'))->toBeTrue()
            ->and($reflection->hasMethod('clean_up'))->toBeTrue();
    });
});
