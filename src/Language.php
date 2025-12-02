<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Language management class
 *
 * Singleton class that manages language loading and provides access to language variables.
 * Use lang() helper or __() function to access language strings.
 */
class Language
{
    private static ?Language $instance = null;
    private array $userLanguage = [];
    private array $sourceLanguage = [];
    private string $currentLanguage = '';
    private string $sourceLanguageCode = 'source';
    private bool $initialized = false;

    private function __construct()
    {
    }

    /**
     * Get the singleton instance of Language
     */
    public static function getInstance(): Language
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the language system (for compatibility)
     */
    public static function init(): Language
    {
        return self::getInstance();
    }

    /**
     * Initialize language loading based on user preferences
     * Maintains compatibility with existing User.php language initialization
     */
    public function initializeLanguage(string $userLang = '', bool $forceReload = false): void
    {
        if ($this->initialized && !$forceReload) {
            return; // Prevent multiple calling, same as existing logic
        }

        // Determine language to use
        if (empty($userLang)) {
            $userLang = config()->get('default_lang', 'en');
        }

        $this->currentLanguage = $userLang;

        // Load source language first
        $this->loadSourceLanguage();

        // Load user language
        $this->loadUserLanguage($userLang);

        // Set locale
        $locale = config()->get("lang.{$userLang}.locale", 'en_US.UTF-8');
        setlocale(LC_ALL, $locale);

        $this->initialized = true;
    }

    /**
     * Load source language (fallback)
     */
    private function loadSourceLanguage(): void
    {
        $sourceFile = LANG_ROOT_DIR . '/source/main.php';
        if (is_file($sourceFile)) {
            $lang = [];
            require $sourceFile;
            $this->sourceLanguage = $lang;
        }
    }

    /**
     * Load user language
     */
    private function loadUserLanguage(string $userLang): void
    {
        $userFile = LANG_ROOT_DIR . '/' . $userLang . '/main.php';
        if (is_file($userFile)) {
            $lang = [];
            require $userFile;
            $this->userLanguage = $lang;
        } else {
            // Fall back to default language if user language doesn't exist
            $defaultFile = LANG_ROOT_DIR . '/' . config()->get('default_lang', 'source') . '/main.php';
            if (is_file($defaultFile)) {
                $lang = [];
                require $defaultFile;
                $this->userLanguage = $lang;
            }
        }

        // Merge with source language as fallback
        $this->userLanguage = array_deep_merge($this->sourceLanguage, $this->userLanguage);
    }

    /**
     * Get a language string by key
     * Supports dot notation for nested arrays (e.g., 'DATETIME.TODAY')
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (str_contains($key, '.')) {
            return $this->getNestedValue($this->userLanguage, $key, $default);
        }

        return $this->userLanguage[$key] ?? $default;
    }

    /**
     * Get a language string from source language
     */
    public function getSource(string $key, mixed $default = null): mixed
    {
        if (str_contains($key, '.')) {
            return $this->getNestedValue($this->sourceLanguage, $key, $default);
        }

        return $this->sourceLanguage[$key] ?? $default;
    }

    /**
     * Check if a language key exists
     */
    public function has(string $key): bool
    {
        if (str_contains($key, '.')) {
            return $this->getNestedValue($this->userLanguage, $key) !== null;
        }

        return array_key_exists($key, $this->userLanguage);
    }

    /**
     * Get all language variables
     */
    public function all(): array
    {
        return $this->userLanguage;
    }

    /**
     * Get all source language variables
     */
    public function allSource(): array
    {
        return $this->sourceLanguage;
    }

    /**
     * Get current language code
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get available languages from config
     */
    public function getAvailableLanguages(): array
    {
        return config()->get('lang', []);
    }

    /**
     * Load additional language file (for modules/extensions)
     */
    public function loadAdditionalFile(string $filename, string $language = ''): bool
    {
        if (empty($language)) {
            $language = $this->currentLanguage;
        }

        $filepath = LANG_ROOT_DIR . '/' . $language . '/' . $filename . '.php';
        if (!is_file($filepath)) {
            // Try source language as fallback
            $filepath = LANG_ROOT_DIR . '/source/' . $filename . '.php';
            if (!is_file($filepath)) {
                return false;
            }
        }

        $lang = [];
        require $filepath;

        // Merge with existing language data
        $this->userLanguage = array_merge($this->userLanguage, $lang);

        return true;
    }

    /**
     * Set a language variable (runtime modification)
     */
    public function set(string $key, mixed $value): void
    {
        if (str_contains($key, '.')) {
            $this->setNestedValue($this->userLanguage, $key, $value);
        } else {
            $this->userLanguage[$key] = $value;
        }
    }

    /**
     * Get nested value using dot notation
     */
    private function getNestedValue(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set nested value using dot notation
     */
    private function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $target = &$array;

        foreach ($keys as $k) {
            if (!isset($target[$k]) || !is_array($target[$k])) {
                $target[$k] = [];
            }
            $target = &$target[$k];
        }

        $target = $value;
    }

    /**
     * Get language name for display
     */
    public function getLanguageName(string $code = ''): string
    {
        if (empty($code)) {
            $code = $this->currentLanguage;
        }

        return config()->get("lang.{$code}.name", $code);
    }

    /**
     * Get language locale
     */
    public function getLanguageLocale(string $code = ''): string
    {
        if (empty($code)) {
            $code = $this->currentLanguage;
        }

        return config()->get("lang.{$code}.locale", 'en_US.UTF-8');
    }

    /**
     * Magic method to allow property access for backward compatibility
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Magic method to allow property setting for backward compatibility
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Magic method to check if property exists
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone()
    {
    }

    /**
     * Prevent serialization of the singleton instance
     */
    public function __serialize(): array
    {
        throw new \LogicException("Cannot serialize a singleton.");
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException("Cannot unserialize a singleton.");
    }
}
