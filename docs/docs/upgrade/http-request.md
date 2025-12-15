---
sidebar_position: 11
title: HTTP Request System
---

# HTTP Request System Migration

TorrentPier has replaced direct superglobal access (`$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_SERVER`, `$_FILES`) with a modern HTTP Request abstraction built on **symfony/http-foundation**.

## Quick Migration Overview

```php
// Old way (deprecated)
$topic_id = (int)$_GET['t'];
$mode = $_REQUEST['mode'] ?? '';
$submit = !empty($_POST['submit']);
$session = $_COOKIE['bb_data'];
$uri = $_SERVER['REQUEST_URI'];
$file = $_FILES['attachment'];

// New way (recommended)
$topic_id = request()->getInt('t');
$mode = request()->getString('mode');
$submit = request()->post->has('submit');
$session = request()->cookies->get('bb_data');
$uri = request()->getRequestUri();
$file = request()->files->get('attachment');
```

## API Reference

### Properties (Symfony InputBag)

Access specific parameter sources directly via properties:

```php
request()->query->get('key')      // GET parameters ($_GET equivalent)
request()->post->get('key')       // POST parameters ($_POST equivalent)
request()->cookies->get('key')    // Cookies ($_COOKIE equivalent)
request()->server->get('key')     // Server variables ($_SERVER equivalent)
request()->headers->get('key')    // HTTP headers
request()->files->get('key')      // Uploaded files ($_FILES equivalent)
```

Each property returns a Symfony bag with methods like `get()`, `has()`, `all()`, `getInt()`, `getBoolean()`, etc.

### Typed Getters (POST > GET Priority)

Convenience methods that check POST first, then GET:

```php
request()->get('key')             // mixed - raw value from POST or GET
request()->has('key')             // bool - check if parameter exists
request()->all()                  // array - all merged parameters
request()->getInt('key')          // int - integer value (default: 0)
request()->getString('key')       // string - string value (default: '')
request()->getBool('key')         // bool - boolean value (default: false)
request()->getFloat('key')        // float - float value (default: 0.0)
request()->getArray('key')        // array - array value (default: [])
```

### Request Metadata

```php
request()->getMethod()            // HTTP method (GET, POST, etc.)
request()->isPost()               // Check if POST request
request()->isGet()                // Check if GET request
request()->isAjax()               // Check if AJAX request
request()->isSecure()             // Check if HTTPS
request()->getClientIp()          // Client IP address
request()->getRequestUri()        // Request URI with query string
request()->getPathInfo()          // Request path without query string
request()->getQueryString()       // Query string only
request()->getHost()              // Host name
request()->getScheme()            // Scheme (http or https)
request()->getUserAgent()         // User-Agent header
request()->getReferer()           // Referer header
request()->getContentType()       // Content-Type header
request()->getContent()           // Raw request body
request()->getSymfonyRequest()    // Underlying Symfony Request object
```

## Migration Examples

### GET Parameters

```php
// Before
$forum_id = isset($_GET['f']) ? (int)$_GET['f'] : 0;
$start = abs((int)($_GET['start'] ?? 0));

// After
$forum_id = request()->query->getInt('f');
$start = abs(request()->query->getInt('start'));
```

### POST Parameters

```php
// Before
$submit = !empty($_POST['submit']);
$message = $_POST['message'] ?? '';

// After
$submit = request()->post->has('submit');
$message = request()->post->get('message', '');
```

### REQUEST (POST > GET)

```php
// Before
$mode = (string)@$_REQUEST['mode'];
$topic_id = (int)$_REQUEST['t'];

// After
$mode = request()->getString('mode');
$topic_id = request()->getInt('t');
```

### Cookies

```php
// Before
$tracks = !empty($_COOKIE[$name]) ? json_decode($_COOKIE[$name], true) : [];

// After
$cookie = request()->cookies->get($name);
$tracks = $cookie ? json_decode($cookie, true) : [];
```

### Server Variables

```php
// Before
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$remote_addr = $_SERVER['REMOTE_ADDR'];

// After
$request_uri = request()->getRequestUri();
$remote_addr = request()->getClientIp();
```

### File Uploads

```php
// Before
if (!empty($_FILES['attachment']['name'])) {
    $file = $_FILES['attachment'];
}

// After
$file = request()->files->get('attachment');
if ($file && $file->isValid()) {
    // Process uploaded file
}
```

## Symfony Request Access

For advanced operations, access the underlying Symfony Request object:

```php
$symfonyRequest = request()->getSymfonyRequest();
```
