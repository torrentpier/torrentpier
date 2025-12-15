---
sidebar_position: 12
title: HTTP Helper
---

# HTTP Helper Migration

The `IsHelper` class has been **renamed** to `HttpHelper` for better clarity and consistency. This change is **breaking** because the old class name is no longer available.

## Migration Strategy

Replace all references of:

```php
\TorrentPier\Helpers\IsHelper
```

With:

```php
\TorrentPier\Helpers\HttpHelper
```

## Usage Examples

```php
use TorrentPier\Helpers\HttpHelper;

// Always use HttpHelper to detect protocol
if (HttpHelper::isHTTPS()) {
    // Handle HTTPS-specific logic
}

// Prefer HttpHelper over deprecated IsHelper
function getBaseUrl(): string {
    $protocol = HttpHelper::isHTTPS() ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
}
```

## Available Methods

The `HttpHelper` class provides static methods for HTTP-related checks:

```php
// Check if current request is over HTTPS
HttpHelper::isHTTPS();
```

## Find and Replace

To migrate your codebase:

```bash
# Search for old class name
grep -r "IsHelper" --include="*.php" .

# Replace in files
sed -i 's/IsHelper/HttpHelper/g' path/to/file.php
```

Or use your IDE's find and replace feature to update all occurrences.
