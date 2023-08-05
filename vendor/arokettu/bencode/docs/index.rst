Bencode
#######

|Packagist| |GitLab| |GitHub| |Bitbucket| |Gitea|

PHP Bencode Encoder/Decoder

Bencode_ is the encoding used by the peer-to-peer file sharing system
BitTorrent_ for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data.

Installation
============

.. code-block:: bash

   # default version:
   composer require 'sandfoxme/bencode'
   # future compatible with 2.x:
   composer require 'sandfoxme/bencode:^1.4 || ^2.4'
   # future compatible with 3.x:
   composer require 'sandfoxme/bencode:^1.7 || ^2.7 || ^3.0'

Documentation
=============

.. toctree::
   :maxdepth: 2

   encoding
   decoding

License
=======

The library is available as open source under the terms of the `MIT License`_.

.. _Bencode:            https://en.wikipedia.org/wiki/Bencode
.. _BitTorrent:         https://en.wikipedia.org/wiki/BitTorrent
.. _MIT License:        https://opensource.org/licenses/MIT

.. |Packagist|  image:: https://img.shields.io/packagist/v/sandfoxme/bencode.svg?style=flat-square
   :target:     https://packagist.org/packages/sandfoxme/bencode
.. |GitHub|     image:: https://img.shields.io/badge/get%20on-GitHub-informational.svg?style=flat-square&logo=github
   :target:     https://github.com/arokettu/bencode
.. |GitLab|     image:: https://img.shields.io/badge/get%20on-GitLab-informational.svg?style=flat-square&logo=gitlab
   :target:     https://gitlab.com/sandfox/bencode
.. |Bitbucket|  image:: https://img.shields.io/badge/get%20on-Bitbucket-informational.svg?style=flat-square&logo=bitbucket
   :target:     https://bitbucket.org/sandfox/bencode
.. |Gitea|      image:: https://img.shields.io/badge/get%20on-Gitea-informational.svg?style=flat-square&logo=gitea
   :target:     https://sandfox.org/sandfox/bencode
