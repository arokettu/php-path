Path Interfaces
###############

PathInterface
=============

``resolveRelative()``
---------------------

.. code-block:: php

    <?php
    public function resolveRelative(RelativePathInterface $path, bool $strict = false): self;

Convert relative path to absolute or combine two relative paths using the caller object as base.


.. code-block:: php

    <?php
    use Arokettu\Path\RelativePath;
    use Arokettu\Path\UnixPath;

    $path = UnixPath::parse('/some/path');
    $rel1 = RelativePath::parse('../other/path');
    // trailing slashes are preserved
    $rel2 = RelativePath::parse('../diff/path/');

    $path->resolveRelative($rel1); // /some/other/path
    // trailing slash will be present if target path has it
    $rel1->resolveRelative($rel2); // ../other/diff/path/

Strict mode throws exception if traversal happens beyond root (no effect if the base path is relative):

.. code-block:: php

    <?php
    use Arokettu\Path\RelativePath;
    use Arokettu\Path\UnixPath;

    $path = UnixPath::parse('/some/path');
    $rel  = RelativePath::parse('../../../../etc/passwd');

    $path->resolveRelative($rel); // /etc/passwd
    $path->resolveRelative($rel, strict: true); // exception

``getPrefix()``
---------------

Path prefix that you can't traverse beyond like root unix path, windows drive path (C:\\), and url hostname.

``getComponents()``
-------------------

An array of path components excluding prefix.
The last component of the path is empty string if path has trailing (back)slash

``isAbsolute()``
----------------

``true`` for instances of ``AbsolutePathInterface``.
``false`` for instances of ``RelativePathInterface``.

``isRelative()``
----------------

``false`` for instances of ``AbsolutePathInterface``.
``true`` for instances of ``RelativePathInterface``.

``toString()`` & ``__toString()``
---------------------------------

Get string value of the path.

AbsolutePathInterface
=====================

``makeRelative()``
------------------

.. code-block:: php

    <?php
    public function makeRelative(self $targetPath, ?\Closure $equals = null): RelativePathInterface;

Make relative path from base path and target path of the same type having equal prefixes.
The paths are treated as case sensitive unless ``$equals`` callback is provided.

.. code-block:: php

    <?php

    use Arokettu\Path\UnixPath;
    use Arokettu\Path\UrlPath;
    use Arokettu\Path\WindowsPath;

    $path1 = UnixPath::parse('/home/arokettu');
    $path2 = UnixPath::parse('/home/sandfox/');
    // there will be a trailing slash if target path has it
    $path1->makeRelative($path2); // ../sandfox/

    // ignore case on Windows
    $path1 = WindowsPath::parse('c:\users\arokettu');
    $path2 = WindowsPath::parse('C:\Users\SandFox');
    $path1->makeRelative(
        $path2,
        fn ($a, $b) => strtoupper($a) === strtoupper($b)
    ); // ..\SandFox

    // resolve urlencoded url path
    $path1 = UrlPath::parse('https://example.com/some%20path/child%20dir');
    $path2 = UrlPath::parse('https://example.com/some path/child dir');
    $path1->makeRelative(
        $path2,
        fn ($a, $b) => urldecode($a) === urldecode($b)
    ); // .

RelativePathInterface
=====================

``isRoot()``
------------

``true`` if the relative path is 'root path', i.e. full path excluding prefix.
Examples:

* ``\Users\SandFox`` for Windows path ``C:\Users\SandFox``
* ``/some path/child dir`` for UrlPath ``https://example.com/some path/child dir``
* Functionally equal to Unix path

When applying root path in ``resolveRelative()``, it replaces the whole path excluding prefix.
