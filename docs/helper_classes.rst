Helper Classes
##############

PathFactory
===========

``parse()``
-----------

.. code-block:: php

    <?php
    PathFactory::parse(
        string $path,
        array $urlSchemes = [],
        array $streamSchemes = []
    ): PathInterface;

The ``parse`` function tries to detect the type of the given string path in the following order:

* Unix path
* Windows path
* Url/Scheme paths:

  * If both ``$urlSchemes`` and ``$streamSchemes`` are empty, all scheme prefixed paths are parsed as URLs.
  * If at least one of the lists is non-empty, all unknown schemes throw an exception.
  * Known schemes are parsed according to which list they belong.
* Relative path of the current OS type.

  * Since there is no way to separate root relative path from Unix path, root relative paths are never returned.

All paths are returned by the ``parse()`` constructor in a non strict mode.

PathUtils
=========

``resolveRelativePath()``
-------------------------

.. code-block:: php

    <?php
    PathUtils::resolveRelativePath(
        string|PathInterface $basePath,
        string|PathInterface $relativePath
    ): string;

Resolves relative path from the base path.

If a string is passed: it goes through ``PathFactory::parse()``.

If ``relativePath`` is an absolute path, it is converted to string and returned.

Otherwise ``$basePath->resolveRelative($relativePath)->toString()`` is returned.

``makeRelativePath()``
----------------------

.. code-block:: php

    <?php
    PathUtils::makeRelativePath(
        string|AbsolutePathInterface $basePath,
        string|AbsolutePathInterface $targetPath
    ): string;

Makes relative path from two absolute paths.

If a string is passed: it goes through ``PathFactory::parse()``.

If any of the paths is a relative path, an exception is thrown.

Otherwise ``$basePath->makeRelative($targetPath)->toString()`` is returned.
