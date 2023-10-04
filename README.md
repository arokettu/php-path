# PHP Path Library

[![Packagist](https://img.shields.io/packagist/v/arokettu/path.svg?style=flat-square)](https://packagist.org/packages/arokettu/path)
[![PHP](https://img.shields.io/packagist/php-v/arokettu/path.svg?style=flat-square)](https://packagist.org/packages/arokettu/path)
[![License](https://img.shields.io/packagist/l/arokettu/path.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Gitlab pipeline status](https://img.shields.io/gitlab/pipeline/sandfox/php-path/master.svg?style=flat-square)](https://gitlab.com/sandfox/php-path/-/pipelines)
[![Codecov](https://img.shields.io/codecov/c/gl/sandfox/php-path?style=flat-square)](https://codecov.io/gl/sandfox/php-path/)

A PHP library to work with absolute and relative paths.

## Usage

```php
<?php

use Arokettu\Path\PathUtils;
use Arokettu\Path\RelativePath;
use Arokettu\Path\UrlPath;

// simple interface

PathUtils::resolveRelativePath('/some/path', '../other/path');
// => /some/other/path
PathUtils::makeRelativePath('/some/path', '/some/other/path');
// => ../other/path

// OOP interface, more control

$url = UrlPath::parse('https://example.com/some/path');
$rel = RelativePath::unix('../other/path');
$url->resolveRelative($rel)->toString();
// => https://example.com/some/other/path
```

## Installation

```bash
composer require arokettu/path
```

## Documentation

Read full documentation here: <https://sandfox.dev/php/path.html>

Also on Read the Docs: <https://php-path.readthedocs.io/>

## Support

Please file issues on our main repo at GitLab: <https://gitlab.com/sandfox/path/-/issues>

Feel free to ask any questions in our room on Gitter: https://gitter.im/arokettu/community

## License

The library is available as open source under the terms of the [MIT License].

[MIT License]: https://opensource.org/licenses/MIT
