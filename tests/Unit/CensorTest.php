<?php

use TorrentPier\Cache\DatastoreManager;
use TorrentPier\Censor;
use TorrentPier\Config;

describe('Censor', function () {
    describe('Core Functionality', function () {
        it('does not censor when disabled', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(false);

            $datastore = Mockery::mock(DatastoreManager::class);
            // datastore->get() should NOT be called when disabled

            $censor = new Censor($config, $datastore);

            expect($censor->isEnabled())->toBeFalse()
                ->and($censor->censorString('fuck'))->toBe('fuck')
                ->and($censor->censorString('any bad word'))->toBe('any bad word');
        });

        it('censors words when enabled', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturn([
                    ['word' => 'fuck', 'replacement' => '****'],
                    ['word' => 'shit', 'replacement' => '####'],
                ]);

            $censor = new Censor($config, $datastore);

            expect($censor->isEnabled())->toBeTrue()
                ->and($censor->getWordsCount())->toBe(2)
                ->and($censor->censorString('fuck you'))->toBe('**** you')
                ->and($censor->censorString('oh shit'))->toBe('oh ####')
                ->and($censor->censorString('hello world'))->toBe('hello world');
        });

        it('handles wildcard patterns', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturn([
                    ['word' => 'f*ck', 'replacement' => '****'],
                ]);

            $censor = new Censor($config, $datastore);

            expect($censor->censorString('fuck'))->toBe('****')
                ->and($censor->censorString('f00ck'))->toBe('****')
                ->and($censor->censorString('faaack'))->toBe('****');
        });

        it('handles empty datastore gracefully', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturn(null);

            $censor = new Censor($config, $datastore);

            expect($censor->getWordsCount())->toBe(0)
                ->and($censor->censorString('anything'))->toBe('anything');
        });
    });

    describe('Runtime Word Addition', function () {
        it('allows adding words at runtime', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturn([]);

            $censor = new Censor($config, $datastore);

            expect($censor->getWordsCount())->toBe(0);

            $censor->addWord('spam', '[SPAM]');

            expect($censor->getWordsCount())->toBe(1)
                ->and($censor->censorString('this is spam'))->toBe('this is [SPAM]');
        });
    });

    describe('Reload Functionality', function () {
        it('reloads words from datastore', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $callCount = 0;
            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturnUsing(function () use (&$callCount) {
                    $callCount++;
                    if ($callCount === 1) {
                        return [['word' => 'old', 'replacement' => '[OLD]']];
                    }

                    return [['word' => 'new', 'replacement' => '[NEW]']];
                });

            $censor = new Censor($config, $datastore);

            expect($censor->censorString('old word'))->toBe('[OLD] word')
                ->and($censor->censorString('new word'))->toBe('new word');

            $censor->reload();

            expect($censor->censorString('old word'))->toBe('old word')
                ->and($censor->censorString('new word'))->toBe('[NEW] word');
        });
    });

    describe('Unicode Support', function () {
        it('handles unicode words correctly', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturn([
                    ['word' => 'блять', 'replacement' => '***'],
                    ['word' => 'хуй', 'replacement' => '###'],
                ]);

            $censor = new Censor($config, $datastore);

            expect($censor->censorString('ну блять'))->toBe('ну ***')
                ->and($censor->censorString('хуй знает'))->toBe('### знает')
                ->and($censor->censorString('привет мир'))->toBe('привет мир');
        });
    });

    describe('Word Boundaries', function () {
        it('respects word boundaries', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('use_word_censor', false)
                ->andReturn(true);

            $datastore = Mockery::mock(DatastoreManager::class);
            $datastore->shouldReceive('get')
                ->with('censor')
                ->andReturn([
                    ['word' => 'ass', 'replacement' => '***'],
                ]);

            $censor = new Censor($config, $datastore);

            // Should censor standalone word
            expect($censor->censorString('kick ass'))->toBe('kick ***')
                ->and($censor->censorString('class'))->toBe('class')
                ->and($censor->censorString('assembly'))->toBe('assembly')
                ->and($censor->censorString('bass'))->toBe('bass');
        });
    });

    afterEach(function () {
        Mockery::close();
    });
});
