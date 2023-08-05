IsResource
##########

|Packagist| |GitLab| |GitHub| |Bitbucket| |Gitea|

The library provides a version of ``is_resource()`` and ``get_resource_type()`` functions that can understand opaque objects.
It is useful tool if you need to support a library that may be affected by the `resource to object migration`__.

.. __: https://github.com/php/php-tasks/issues/6

Installation
============

.. code-block:: bash

   composer require 'arokettu/is-resource'

Documentation
=============

.. toctree::
   :maxdepth: 2

   functions
   extensions

License
=======

The library is available as open source under the terms of the `MIT License`_.

.. _MIT License:        https://opensource.org/licenses/MIT

.. |Packagist|  image:: https://img.shields.io/packagist/v/arokettu/is-resource.svg?style=flat-square
   :target:     https://packagist.org/packages/arokettu/is-resource
.. |GitHub|     image:: https://img.shields.io/badge/get%20on-GitHub-informational.svg?style=flat-square&logo=github
   :target:     https://github.com/arokettu/is-resource
.. |GitLab|     image:: https://img.shields.io/badge/get%20on-GitLab-informational.svg?style=flat-square&logo=gitlab
   :target:     https://gitlab.com/sandfox/is-resource
.. |Bitbucket|  image:: https://img.shields.io/badge/get%20on-Bitbucket-informational.svg?style=flat-square&logo=bitbucket
   :target:     https://bitbucket.org/sandfox/is-resource
.. |Gitea|      image:: https://img.shields.io/badge/get%20on-Gitea-informational.svg?style=flat-square&logo=gitea
   :target:     https://sandfox.org/sandfox/is-resource
