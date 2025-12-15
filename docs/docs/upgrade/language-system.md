---
sidebar_position: 7
title: Language System
---

# Language System Migration

TorrentPier has modernized its language system with DI container integration while maintaining 100% backward compatibility with the existing global `$lang` variable.

## No Code Changes Required

**Important**: All existing `global $lang` calls continue to work exactly as before. This is an internal modernization that requires **zero code changes** in your application.

```php
// All existing code continues to work unchanged
global $lang;
echo $lang['FORUM'];
echo $lang['DATETIME']['TODAY'];
```

## Key Improvements

### Modern Foundation

- **DI Container**: Managed via Application container for efficient memory usage
- **Centralized Management**: Single point of control for language loading and switching
- **Type Safety**: Better error detection and IDE support
- **Dot Notation Support**: Access nested language arrays with simple syntax

### Enhanced Functionality

- **Automatic Fallback**: Source language fallback for missing translations
- **Dynamic Loading**: Load additional language files for modules/extensions
- **Runtime Modification**: Add or modify language strings at runtime
- **Locale Management**: Automatic locale setting based on language selection

## Enhanced Capabilities

New code can leverage the modern Language class features with convenient shorthand functions:

```php
// Convenient shorthand functions (recommended for frequent use)
echo __('FORUM');                           // Same as lang()->get('FORUM')
echo __('DATETIME.TODAY');                  // Dot notation for nested arrays
_e('WELCOME_MESSAGE');                      // Echo shorthand
$message = __('CUSTOM_MESSAGE', 'Default'); // With default value

// Full lang() helper access (for advanced features)
echo lang()->get('FORUM');
echo lang()->get('DATETIME.TODAY');  // Dot notation for nested arrays

// Check if language key exists
if (lang()->has('ADVANCED_FEATURE')) {
    echo __('ADVANCED_FEATURE');
}

// Get current language information
$currentLang = lang()->getCurrentLanguage();
$langName = lang()->getLanguageName();
$langLocale = lang()->getLanguageLocale();

// Load additional language files for modules
lang()->loadAdditionalFile('custom_module', 'en');

// Runtime language modifications
lang()->set('CUSTOM_KEY', 'Custom Value');
lang()->set('NESTED.KEY', 'Nested Value');
```

## Language Management

### Available Languages

```php
// Get all available languages from configuration
$availableLanguages = lang()->getAvailableLanguages();

// Get language display name
$englishName = lang()->getLanguageName('en');  // Returns: "English"
$currentName = lang()->getLanguageName();       // Current language name

// Get language locale for formatting
$locale = lang()->getLanguageLocale('ru');      // Returns: "ru_RU.UTF-8"
```

### Dynamic Language Loading

```php
// Load additional language files (useful for modules/plugins)
$success = lang()->loadAdditionalFile('torrent_management');
if ($success) {
    echo lang()->get('TORRENT_UPLOADED');
}

// Load from specific language
lang()->loadAdditionalFile('admin_panel', 'de');
```

### Runtime Modifications

```php
// Set custom language strings
lang()->set('SITE_WELCOME', 'Welcome to Our Tracker!');
lang()->set('ERRORS.INVALID_TORRENT', 'Invalid torrent file');

// Modify existing strings
lang()->set('LOGIN', 'Sign In');
```

## Backward Compatibility Features

The Language class automatically maintains all global variables:

```php
// Global variable is automatically updated by the Language class
global $lang;

// When you call lang()->set(), global is updated
lang()->set('CUSTOM', 'Value');
echo $lang['CUSTOM'];  // Outputs: "Value"

// When language is initialized, $lang is populated
// $lang contains user language + source language fallbacks
```

## Convenient Shorthand Functions

For frequent language access, TorrentPier provides convenient shorthand functions:

```php
// __() - Get language string (most common)
echo __('FORUM');                    // Returns: "Forum"
echo __('DATETIME.TODAY');           // Nested access: "Today"
$msg = __('MISSING_KEY', 'Default'); // With default value

// _e() - Echo language string directly
_e('WELCOME_MESSAGE');               // Same as: echo __('WELCOME_MESSAGE')
_e('USER_ONLINE', 'Online');         // With default value

// Common usage patterns
$title = __('PAGE_TITLE', config()->get('sitename'));
$error = __('ERROR.INVALID_INPUT', 'Invalid input');
```

Comparison with verbose syntax:

```php
// Before (verbose)
echo lang()->get('FORUM');
echo lang()->get('DATETIME.TODAY');
$msg = lang()->get('WELCOME', 'Welcome');

// After (concise)
echo __('FORUM');
echo __('DATETIME.TODAY');
$msg = __('WELCOME', 'Welcome');
```

## Magic Methods Support

```php
// Magic getter (same as lang()->get())
$welcome = lang()->WELCOME;
$today = lang()->{'DATETIME.TODAY'};

// Magic setter (same as lang()->set())
lang()->CUSTOM_MESSAGE = 'Hello World';
lang()->{'NESTED.KEY'} = 'Nested Value';

// Magic isset
if (isset(lang()->ADVANCED_FEATURE)) {
    // Language key exists
}
```

## Performance Benefits

While maintaining compatibility, you get:

- **Single Language Loading**: Languages loaded once and cached in container
- **Memory Efficiency**: No duplicate language arrays across application
- **Automatic Locale Setting**: Proper locale configuration for date/time formatting
- **Fallback Chain**: Source language → Default language → Requested language

## Verification

To verify the migration is working correctly:

```php
// Test convenient shorthand functions
echo "Forum text: " . __('FORUM');
echo "Today text: " . __('DATETIME.TODAY');
_e('INFORMATION');  // Echo directly

// Test with default values
echo "Custom: " . __('CUSTOM_KEY', 'Default Value');

// Test full lang() helper access
echo "Current language: " . lang()->getCurrentLanguage();
echo "Language name: " . lang()->getLanguageName();

// Test backward compatibility
global $lang;
echo "Global access: " . $lang['FORUM'];

// Verify globals are synchronized
lang()->set('TEST_KEY', 'Test Value');
echo "Sync test: " . $lang['TEST_KEY'];  // Should output: "Test Value"
```
