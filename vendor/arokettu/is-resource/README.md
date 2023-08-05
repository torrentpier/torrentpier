# IsResource: PHP Resource Compatibility Helper

[![Packagist](https://img.shields.io/packagist/v/arokettu/is-resource.svg?style=flat-square)](https://packagist.org/packages/arokettu/is-resource)
[![PHP](https://img.shields.io/packagist/php-v/arokettu/is-resource.svg?style=flat-square)](https://packagist.org/packages/arokettu/is-resource)
[![Packagist](https://img.shields.io/github/license/arokettu/is-resource.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Gitlab pipeline status](https://img.shields.io/gitlab/pipeline/sandfox/is-resource/master.svg?style=flat-square)](https://gitlab.com/sandfox/is-resource/-/pipelines)

``is_resource()`` and ``get_resource_type()`` that work with opaque objects.

## Usage

```php
<?php

use Arokettu\IsResource as r;

$hash = hash_init('md5');

// vanilla functions:
is_resource($hash); // true in PHP <= 7.1, false in PHP >= 7.2
get_resource_type($hash); // "Hash Context" in PHP <= 7.1, null or TypeError in PHP >= 7.2

// library functions:
r\is_resource($hash); // true
r\get_resource_type($hash); // "Hash Context"
```

## Installation

```bash
composer require arokettu/is-resource
```

## Documentation

Read full documentation here: <https://sandfox.dev/php/is-resource.html>

Also on Read the Docs: <https://is-resource.readthedocs.io/>

## License

The library is available as open source under the terms of the [MIT License].

[MIT License]:  https://opensource.org/licenses/MIT
