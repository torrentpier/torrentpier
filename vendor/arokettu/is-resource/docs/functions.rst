Functions
#########

``is_resource()``
=================

.. tip:: Base function: https://www.php.net/manual/en/function.is-resource.php

.. code-block:: php

    <?php

    function \Arokettu\IsResource\is_resource(mixed $value): bool;

The function returns ``true`` if ``$value`` is

* a resource
* an opaque object that was a resource in earlier PHP versions

``get_resource_type()``
=======================

.. tip:: Base function: https://www.php.net/manual/en/function.get-resource-type.php

.. code-block:: php

    <?php

    function \Arokettu\IsResource\get_resource_type(resource|object $resource): string;

The function returns the resource type string if ``$resource`` is

* a resource
* an opaque object that was a resource in earlier PHP versions

In other cases the behavior falls back to the default ``\get_resource_type()`` behavior:

* in PHP < 8.0: return ``null`` + issue ``E_WARNING``
* in PHP >= 8.0: throw ``TypeError``

``try_get_resource_type()``
===========================

.. code-block:: php

    <?php

    function \Arokettu\IsResource\try_get_resource_type(mixed $resource): string|null;

A useful shortcut to check the resource type.
It returns null in case the resource was not recognized.

.. code-block:: php

    <?php

    use function Arokettu\IsResource\try_get_resource_type;

    // was:
    if (is_resource($conn) && get_resource_type($conn) === 'pgsql link') {
        // ...
    }

    // With PHP 8.1 this transforms to:
    if (is_resource($conn) && get_resource_type($conn) === 'pgsql link' || $conn instanceof PgSql\Connection) {
        // ...
    }

    // but with try_get_resource_type() from this library it's just this:
    if (try_get_resource_type($conn) === 'pgsql link') {
        // ...
    }
