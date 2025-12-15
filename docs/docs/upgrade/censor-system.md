---
sidebar_position: 8
title: Censor System
---

# Censor System Migration

The word censoring system has been refactored to use DI container, similar to the Configuration system, providing better performance and consistency.

## Quick Migration Overview

```php
// Old way (still works, but not recommended)
global $wordCensor;
$censored = $wordCensor->censorString($text);

// New way (recommended)
$censored = censor()->censorString($text);
```

## Key Censor Changes

### Basic Usage

```php
// Censor a string
$text = "This contains badword content";
$censored = censor()->censorString($text);

// Check if censoring is enabled
if (censor()->isEnabled()) {
    $censored = censor()->censorString($text);
} else {
    $censored = $text;
}

// Get count of loaded censored words
$wordCount = censor()->getWordsCount();
```

### Advanced Usage

```php
// Add runtime censored words (temporary, not saved to database)
censor()->addWord('badword', '***');
censor()->addWord('anotherbad*', 'replaced'); // Wildcards supported

// Reload censored words from database (useful after admin updates)
censor()->reload();

// Check if censoring is enabled
$isEnabled = censor()->isEnabled();
```

## Backward Compatibility

The global `$wordCensor` variable is still available and works exactly as before:

```php
// This still works - backward compatibility maintained
global $wordCensor;
$censored = $wordCensor->censorString($text);

// But this is now preferred
$censored = censor()->censorString($text);
```

## Performance Benefits

- **Single Instance**: Only one censor instance loads words from database
- **Automatic Reloading**: Words are automatically reloaded when updated in admin panel
- **Memory Efficient**: Shared instance across entire application
- **Lazy Loading**: Words only loaded when censoring is enabled

## Admin Panel Updates

When you update censored words in the admin panel, the system now automatically:
1. Updates the datastore cache
2. Reloads the Censor instance with fresh words
3. Applies changes immediately without requiring page refresh

## Best Practices

### Check Before Processing

```php
function processUserInput(string $text): string {
    if (censor()->isEnabled()) {
        return censor()->censorString($text);
    }
    return $text;
}
```

### Use Helper Consistently

```php
$censoredText = censor()->censorString($input);
```

### Class Integration

```php
class ForumPost {
    public function getDisplayText(): string {
        return censor()->censorString($this->text);
    }
}
```

### Add Runtime Words When Needed

```php
function setupCustomCensoring(): void {
    if (isCustomModeEnabled()) {
        censor()->addWord('custombad*', '[censored]');
    }
}
```
