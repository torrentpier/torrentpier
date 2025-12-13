<?php

use TorrentPier\Config;
use TorrentPier\Language;

// Define BB_PATH if not exists (used by Language class)
if (!defined('BB_PATH')) {
    define('BB_PATH', dirname(__DIR__, 2));
}

describe('Language', function () {
    it('can be instantiated with mocked config', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);

        expect($instance)->toBeInstanceOf(Language::class);
    });

    it('has empty currentLanguage by default', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);

        expect($instance->currentLanguage)->toBe('');
    });

    it('returns default when key not found', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);

        expect($instance->get('nonexistent', 'default_value'))->toBe('default_value');
    });

    it('allows setting and getting values', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);
        $instance->set('TEST_KEY', 'test_value');

        expect($instance->get('TEST_KEY'))->toBe('test_value');
    });

    it('supports dot notation for nested values', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);
        $instance->set('NESTED.KEY', 'nested_value');

        expect($instance->get('NESTED.KEY'))->toBe('nested_value');
    });

    it('checks if key exists with has()', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);
        $instance->set('EXISTS', 'value');

        expect($instance->has('EXISTS'))->toBeTrue()
            ->and($instance->has('NOT_EXISTS'))->toBeFalse();
    });

    it('returns all language variables', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);
        $instance->set('KEY1', 'value1');
        $instance->set('KEY2', 'value2');

        expect($instance->all())->toHaveKey('KEY1')
            ->and($instance->all())->toHaveKey('KEY2');
    });

    it('supports magic property access', function () {
        $config = Mockery::mock(Config::class);

        $instance = new Language($config);
        $instance->MAGIC_KEY = 'magic_value';

        expect($instance->MAGIC_KEY)->toBe('magic_value')
            ->and(isset($instance->MAGIC_KEY))->toBeTrue();
    });

    it('gets available languages from config', function () {
        $languages = [
            'en' => ['name' => 'English', 'locale' => 'en_US.UTF-8'],
            'ru' => ['name' => 'Russian', 'locale' => 'ru_RU.UTF-8'],
        ];

        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')
            ->with('lang', [])
            ->andReturn($languages);

        $instance = new Language($config);

        expect($instance->getAvailableLanguages())->toBe($languages);
    });

    afterEach(function () {
        Mockery::close();
    });
});
