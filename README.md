# HTMLMinifier

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
