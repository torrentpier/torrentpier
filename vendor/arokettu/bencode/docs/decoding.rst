Decoding
########

Scalars
=======

Scalars will be converted to their respective types.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "d" .
        "3:arrli1ei2ei3ei4ee" .
        "4:booli1e" .
        "5:float6:3.1415" .
        "3:inti123e" .
        "6:string9:test\0test" .
        "e"
    );
    // [
    //   "arr" => [1,2,3,4],
    //   "bool" => 1,
    //   "float" => "3.1415",
    //   "int" => 123,
    //   "string" => "test\0test",
    // ]

Please note that floats and booleans will stay converted because Bencode has no native support for these types.

Lists and Dictionaries
======================

.. deprecated:: 1.4/2.3 ``dictionaryType`` replaced with ``dictType``

Dictionaries and lists will be arrays by default.
You can change this behavior with options.
Use ``Collection`` constants for built in behaviors:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode("...", [
        // this is a default for both listType and dictType
        'listType' => Bencode\Collection::ARRAY,
        // convert to stdClass
        'dictType' => Bencode\Collection::OBJECT,
    ]);

Or use advanced control with class names or callbacks:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode("...", [
        // pass class name, new $type($array) will be created
        'dictType' => ArrayObject::class,
        // or callback for greater flexibility
        'listType' => function ($array) {
            return new ArrayObject(
                $array,
                ArrayObject::ARRAY_AS_PROPS
            );
        },
    ]);

.. _bencode_decoding_bigint:

Big Integers
============

By default the library only works with a native integer type but if you need to use large integers,
for example, if you want to parse a torrent file for a >= 4GB file on a 32 bit system,
you can enable big integer support.

External Libraries
------------------

.. versionadded:: 1.5/2.5 GMP support
.. versionadded:: 1.6/2.6 Pear's Math_BigInteger, brick/math
.. deprecated:: 1.7/2.7 Use ``'bigInt' => Bencode\BigInt::GMP`` instead of ``useGMP: true``

.. important::
    These math libraries are not explicit dependencies of this library.
    Install them separately before enabling.

Supported libraries:

* `GNU Multiple Precision PHP Extension <GMP_>`_
* `brick/math`_
* PEAR's `Math_BigInteger`_

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // GMP
    // 1.6+
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['bigInt' => Bencode\BigInt::GMP]
    );
    // 1.5 (deprecated)
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['useGMP' => true]
    );
    //  ['int' => gmp_init(
    //      '79228162514264337593543950336'
    //  )]

    // brick/math
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['bigInt' => Bencode\BigInt::BRICK_MATH]
    );
    //  ['int' => \Brick\Math\BigInteger::of(
    //      '79228162514264337593543950336'
    //  )]

    // Math_BigInteger from PEAR
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['bigInt' => Bencode\BigInt::PEAR]
    );
    //  ['int' => new \Math_BigInteger(
    //      '79228162514264337593543950336'
    //  )]

.. _GMP: https://www.php.net/manual/en/book.gmp.php
.. _brick/math: https://github.com/brick/math
.. _Math_BigInteger: https://pear.php.net/package/Math_BigInteger

Internal Type
-------------

.. versionadded:: 1.6/2.6

The library also has built in ``BigIntType``.
It does not require any external dependencies but also does not allow any manipulation.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['bigInt' => Bencode\BigInt::INTERNAL]
    );
    //  ['int' => new \SandFox\Bencode\Types\BigIntType(
    //      '79228162514264337593543950336'
    //  )]

BigIntType is a value object with several getters:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // simple string representation:
    $str = $data->getValue();
    // converters to the supported libraries:
    $obj = $data->toGMP();
    $obj = $data->toPear();
    $obj = $data->toBrickMath();

Custom Handling
---------------

.. versionadded:: 1.6/2.6

Like listType and dictType you can use a callable or a class name:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['bigInt' => fn (string $v) => $v]
    ); // ['int' => '79228162514264337593543950336']
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        ['bigInt' => MyBigIntHandler::class]
    );
    //  ['int' => new MyBigIntHandler(
    //      '79228162514264337593543950336'
    //  )]

Working with files
==================

Load data from a file:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::load('testfile.torrent');

Working with streams
====================

.. versionadded:: 1.5/2.5

Load data from a seekable readable stream:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decodeStream(fopen('...', 'r'));

Decoder object
==============

.. versionadded:: 1.7/2.7/3.0

Decoder object can be configured on creation and used multiple times:

.. code-block:: php

    <?php

    use SandFox\Bencode\Decoder;

    $decoder = new Decoder(['bigInt' => Bencode\BigInt::INTERNAL]);
    // all calls available:
    $decoder->decode($encoded);
    $decoder->decodeStream($encoded, $stream);
    $decoder->load($filename);
