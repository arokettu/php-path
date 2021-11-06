Path Classes
############

RelativePath
============

The only concrete implementation of ``RelativePathInterface``.
In non-root relative paths first component returned by ``getComponents()`` is either ``'.'`` or ``'..'``.

When resolving relative, windows-ness of the resulting relative will be inherited from the base path.

Available constructors:

.. code-block:: php

    <?php
    new RelativePath(string $path, bool $windows = false);

``$windows = false``: Unix-like path. Path separators are slashes.

``$windows = true``: Windows-like path. Path separators are both slashes and backslashes.
When exporting a string, backslashes are used.

.. code-block:: php

    <?php
    RelativePath::unix(string $path): self;

Same as ``new RelativePath($path, windows: false)``

.. code-block:: php

    <?php
    RelativePath::windows(string $path): self;

Same as ``new RelativePath($path, windows: true)``

.. code-block:: php

    <?php
    RelativePath::currentOS(string $path): self;

If Windows is detected, create a Windows-like path, otherwise create Unix-like path.

.. note:: Windows is detected by the ``DIRECTORY_SEPARATOR`` constant.

.. code-block:: php

    <?php
    RelativePath::parse(string $path): self;

Alias of ``currentOS()``.

FilesystemPath
==============

A base class for ``UnixPath`` and ``WindowsPath``.

No default constructor, only a named constructor is available:

.. code-block:: php

    <?php
    FilesystemPath::parse(string $path, bool $strict = false): self;

If Windows is detected, create a Windows path, otherwise create a Unix path.
Strict mode does not allow to have relative components that traverse beyond root.

.. note:: Windows is detected by the ``DIRECTORY_SEPARATOR`` constant.

.. code-block:: php

    <?php
    use Arokettu\Path\FilesystemPath;

    // on windows
    FilesystemPath::parse('C:\Windows\..\..\..\Users'); // C:\Users
    FilesystemPath::parse('C:\Windows\..\..\..\Users' strict: true); // exception

UnixPath
--------

A class for Unix paths.
The prefix is ``'/'``

.. code-block:: php

    <?php
    // these are equal
    new UnixPath(string $path, bool $strict = false);
    UnixPath::parse(string $path, bool $strict = false): self;

WindowsPath
-----------

.. warning::
    Windows usually have much more restrictions on file path than unix-like operating systems
    like forbidding characters like ``|`` and ``:``.
    The library doesn't check for that even in strict mode.

A class for Windows paths.
``makeRelative()`` returns relatives of the Windows-like type.

Supported paths:

* DOS-like paths.
  The classic paths with a drive letter: ``C:\Path``.
  Both slashes and backslashes are supported as component separators.
  Relative components are resolved on creation like in most other classes here.
  The prefix here is a drive letter.
* UNC paths.
  Examples:

  * Local paths like ``\\*\C:\Path``. The prefix here is ``\\*\C:\``.
  * Network paths like ``\\AROKETTUPC\c$``. The prefix here is ``\\AROKETTUPC\``.

  UNC paths do not allow forward slashes and relative components.

.. note::
    Relative paths with drive letter like ``C:Path\Path`` are valid in Windows
    but are not supported by the library in any way.

.. code-block:: php

    <?php
    // these are equal
    new WindowsPath(string $path, bool $strict = false);
    WindowsPath::parse(string $path, bool $strict = false): self;

UrlPath
=======

A class for URL paths.
The prefix is scheme + hostname.

.. code-block:: php

    <?php
    // these are equal
    new UrlPath(string $path, bool $strict = false);
    UrlPath::parse(string $path, bool $strict = false): self;

StreamPath
==========

A class for PHP stream like paths.
Examples include php streams like ``php://temp``.
It can be useful with libraries that create virtual file systems like `adlawson/vfs`_ and `mikey179/vfsstream`_.
The prefix is scheme.

.. _adlawson/vfs: https://packagist.org/packages/adlawson/vfs
.. _mikey179/vfsstream: https://packagist.org/packages/mikey179/vfsstream

.. code-block:: php

    <?php
    // these are equal
    new StreamPath(string $path, bool $strict = false);
    StreamPath::parse(string $path, bool $strict = false): self;
