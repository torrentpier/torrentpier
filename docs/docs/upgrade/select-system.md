---
sidebar_position: 9
title: Select System
---

# Select System Migration

The Select class has been moved and reorganized for better structure and consistency within the legacy system organization.

## Quick Migration Overview

```php
// Old way (deprecated)
\TorrentPier\Legacy\Select::language($new['default_lang'], 'default_lang');
\TorrentPier\Legacy\Select::timezone('', 'timezone_type');
\TorrentPier\Legacy\Select::template($pr_data['tpl_name'], 'tpl_name');

// New way (recommended)
\TorrentPier\Legacy\Common\Select::language($new['default_lang'], 'default_lang');
\TorrentPier\Legacy\Common\Select::timezone('', 'timezone_type');
\TorrentPier\Legacy\Common\Select::template($pr_data['tpl_name'], 'tpl_name');
```

## Namespace Update

The Select class has been moved from `\TorrentPier\Legacy\Select` to `\TorrentPier\Legacy\Common\Select` to better organize legacy components.

## Method Usage Remains Unchanged

```php
// Language selection dropdown
$languageSelect = \TorrentPier\Legacy\Common\Select::language($currentLang, 'language_field');

// Timezone selection dropdown
$timezoneSelect = \TorrentPier\Legacy\Common\Select::timezone($currentTimezone, 'timezone_field');

// Template selection dropdown
$templateSelect = \TorrentPier\Legacy\Common\Select::template($currentTemplate, 'template_field');
```

## Available Select Methods

All existing methods remain available:

```php
\TorrentPier\Legacy\Common\Select::language($selected, $name);
\TorrentPier\Legacy\Common\Select::timezone($selected, $name);
\TorrentPier\Legacy\Common\Select::template($selected, $name);
```

## Backward Compatibility

The old class path is deprecated but still works through class aliasing:

```php
// This still works but is deprecated
\TorrentPier\Legacy\Select::language($lang, 'default_lang');

// This is the new recommended way
\TorrentPier\Legacy\Common\Select::language($lang, 'default_lang');
```

## Migration Strategy

1. **Search and Replace**: Update all references to the old namespace
2. **Import Statements**: Update use statements if you're using them
3. **Configuration Files**: Update any configuration that references the old class path

```php
// Update use statements
// Old
use TorrentPier\Legacy\Select;

// New
use TorrentPier\Legacy\Common\Select;
```

## Best Practices

### Use New Namespace Consistently

```php
$languageSelect = \TorrentPier\Legacy\Common\Select::language($currentLang, 'language_field');
```

### Store Frequently Used Selects

```php
class AdminPanel {
    private string $languageSelect;
    private string $timezoneSelect;

    public function __construct() {
        $this->languageSelect = \TorrentPier\Legacy\Common\Select::language('', 'default_lang');
        $this->timezoneSelect = \TorrentPier\Legacy\Common\Select::timezone('', 'timezone');
    }
}
```
