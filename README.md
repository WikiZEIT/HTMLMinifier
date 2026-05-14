# HTMLMinifier

[![packagist](https://img.shields.io/badge/packagist-0.1.0-blue.svg)](https://packagist.org/packages/wikizeit/html-minifier)
[![CI](https://github.com/WikiZEIT/HTMLMinifier/actions/workflows/test.yaml/badge.svg)](https://github.com/WikiZEIT/HTMLMinifier/actions/workflows/test.yaml)
[![HTMLMinifier GitHub repo](https://img.shields.io/badge/github-HTMLMinifier-orange?logo=github)](https://github.com/WikiZEIT/HTMLMinifier)
[![Coverage Status](https://coveralls.io/repos/github/WikiZEIT/HTMLMinifier/badge.svg?branch=master)](https://coveralls.io/github/WikiZEIT/HTMLMinifier?branch=master)
[![LICENSE MIT](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/jcubic/horavox/blob/master/LICENSE)

Lightweight PHP HTML minifier that uses
[wikimedia/minify](https://packagist.org/packages/wikimedia/minify) for inline JavaScript and CSS
minification.

## Features

- Removes HTML comments (with configurable patterns to preserve)
- Collapses whitespace between tags
- Minifies inline `<script>` blocks via Wikimedia's `JavaScriptMinifier`
- Minifies inline `<style>` blocks via Wikimedia's `CSSMin`
- Preserves content in `<pre>`, `<code>`, and `<textarea>` tags
- Preserves IE conditional comments
- Skips non-JavaScript script types (e.g. `application/ld+json`)

## Installation

```bash
composer require wikizeit/html-minifier
```

## Usage

### Static method

```php
use WikiZEIT\HTMLMinifier;

$minified = HTMLMinifier::minify($html);
```

### Instance with custom preserved comments

```php
use WikiZEIT\HTMLMinifier;

$minifier = new HTMLMinifier();
$minifier->preserveComment('/^\s*my-placeholder\s*$/');
$minified = $minifier->run($html);
```

### With output buffering

```php
<?php
ob_start();
require_once __DIR__ . '/vendor/autoload.php';
?>
<!DOCTYPE html>
<html>
  <!-- ... your HTML ... -->
</html>
<?php
echo \WikiZEIT\HTMLMinifier::minify(ob_get_clean());
?>
```

## License

Copyright (c) 2026 [Jakub T. Jankiewicz](https://jakub.jankiewicz.org/)

Released under the MIT License. See
[LICENSE](https://github.com/WikiZEIT/HTMLMinifier/blob/master/LICENSE) for details.
