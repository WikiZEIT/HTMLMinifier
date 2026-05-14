<?php

error_reporting(E_ALL);

use PHPUnit\Framework\TestCase;
use WikiZEIT\HTMLMinifier;

class HTMLMinifierTest extends TestCase
{
    public function testCollapseWhitespace(): void
    {
        $html = "<div>   hello   world   </div>";
        $result = HTMLMinifier::minify($html);
        $this->assertSame('<div> hello world </div>', $result);
    }

    public function testRemoveWhitespaceBetweenTags(): void
    {
        $html = "<div>  </div>  \n  <p>text</p>";
        $result = HTMLMinifier::minify($html);
        $this->assertSame('<div></div><p>text</p>', $result);
    }

    public function testRemoveHtmlComments(): void
    {
        $html = '<div><!-- remove this -->text</div>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame('<div>text</div>', $result);
    }

    public function testPreserveIeConditionalComments(): void
    {
        $html = '<div><!--[if IE]><p>old</p><![endif]--></div>';
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('<!--[if IE]>', $result);
    }

    public function testPreserveCustomCommentPattern(): void
    {
        $html = '<div><!-- form-message-placeholder --></div>';
        $minifier = new HTMLMinifier();
        $minifier->preserveComment('/^\s*form-message-placeholder\s*$/');
        $result = $minifier->run($html);
        $this->assertStringContainsString('<!-- form-message-placeholder -->', $result);
    }

    public function testPreservePreContent(): void
    {
        $html = "<pre>\n  line 1\n  line 2\n</pre>";
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString("  line 1\n  line 2", $result);
    }

    public function testPreserveCodeContent(): void
    {
        $html = "<code>  var x = 1;  </code>";
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('  var x = 1;  ', $result);
    }

    public function testPreserveTextareaContent(): void
    {
        $html = "<textarea>  some\n  text  </textarea>";
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString("  some\n  text  ", $result);
    }

    public function testMinifyInlineScript(): void
    {
        $html = '<script>  var x = 1;  var y = 2;  </script>';
        $result = HTMLMinifier::minify($html);
        $this->assertStringNotContainsString('  var x', $result);
        $this->assertStringContainsString('var x=1;', $result);
    }

    public function testMinifyInlineStyle(): void
    {
        $html = "<style>\n  body {\n    color: red;\n  }\n</style>";
        $result = HTMLMinifier::minify($html);
        $this->assertStringNotContainsString("\n", $result);
        $this->assertStringContainsString('body', $result);
        $this->assertStringContainsString('color', $result);
    }

    public function testMinifyJsonLdScript(): void
    {
        $html = '<script type="application/ld+json">{
            "name":   "test",
            "url":    "https://example.com/path"
        }</script>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame(
            '<script type="application/ld+json">{"name":"test","url":"https://example.com/path"}</script>',
            $result
        );
    }

    public function testMinifyApplicationJsonScript(): void
    {
        $html = '<script type="application/json">{  "key":  "value"  }</script>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame(
            '<script type="application/json">{"key":"value"}</script>',
            $result
        );
    }

    public function testInvalidJsonThrows(): void
    {
        $html = '<script type="application/ld+json">{ invalid }</script>';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON');
        HTMLMinifier::minify($html);
    }

    public function testJsonLdPreservesUnicode(): void
    {
        $html = '<script type="application/ld+json">{ "name": "Jakub Jankiewicz" }</script>';
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('Jakub Jankiewicz', $result);
        $this->assertStringNotContainsString('\\u', $result);
    }

    public function testJsonLdPreservesSlashes(): void
    {
        $html = '<script type="application/ld+json">{ "url": "https://example.com/foo/bar" }</script>';
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('https://example.com/foo/bar', $result);
        $this->assertStringNotContainsString('\\/', $result);
    }

    public function testJsonLdNestedObject(): void
    {
        $html = '<script type="application/ld+json">{
            "@context": "https://schema.org",
            "@type":    "Person",
            "address":  {
                "@type":  "PostalAddress",
                "country": "PL"
            }
        }</script>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame(
            '<script type="application/ld+json">{"@context":"https://schema.org","@type":"Person","address":{"@type":"PostalAddress","country":"PL"}}</script>',
            $result
        );
    }

    public function testJsonLdArray(): void
    {
        $html = '<script type="application/ld+json">[
            { "name": "one" },
            { "name": "two" }
        ]</script>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame(
            '<script type="application/ld+json">[{"name":"one"},{"name":"two"}]</script>',
            $result
        );
    }

    public function testEmptyJsonLdScript(): void
    {
        $html = '<script type="application/ld+json"></script>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame('<script type="application/ld+json"></script>', $result);
    }

    public function testJsonLdWithSingleQuotedType(): void
    {
        $html = "<script type='application/ld+json'>{  \"key\": \"val\"  }</script>";
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('{"key":"val"}', $result);
    }

    public function testPreserveNonJsScriptType(): void
    {
        $html = '<script type="text/template">  {{ hello }}  </script>';
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('  {{ hello }}  ', $result);
    }

    public function testMinifyTextJavascriptType(): void
    {
        $html = '<script type="text/javascript">  var x = 1;  </script>';
        $result = HTMLMinifier::minify($html);
        $this->assertStringContainsString('var x=1;', $result);
    }

    public function testEmptyScript(): void
    {
        $html = '<script></script>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame('<script></script>', $result);
    }

    public function testEmptyStyle(): void
    {
        $html = '<style></style>';
        $result = HTMLMinifier::minify($html);
        $this->assertSame('<style></style>', $result);
    }

    public function testFullDocument(): void
    {
        $html = <<<'HTML'
        <!DOCTYPE html>
        <html lang="pl">
          <head>
            <meta charset="utf-8">
            <title>Test</title>
            <style>
              body {
                  margin: 0;
                  padding: 0;
              }
            </style>
          </head>
          <body>
            <!-- comment -->
            <h1>Hello</h1>
            <pre>
              keep this
            </pre>
            <script>
              var x = 1;
              console.log(x);
            </script>
          </body>
        </html>
        HTML;

        $result = HTMLMinifier::minify($html);

        $this->assertStringNotContainsString('<!-- comment -->', $result);
        $this->assertStringContainsString("keep this", $result);
        $this->assertStringContainsString('var x=1;', $result);
        $this->assertLessThan(strlen($html), strlen($result));
    }

    public function testStaticMinifyAndInstanceRunProduceSameResult(): void
    {
        $html = '<div>  <p>  text  </p>  </div>';
        $static = HTMLMinifier::minify($html);
        $instance = (new HTMLMinifier())->run($html);
        $this->assertSame($static, $instance);
    }

    public function testReusableInstance(): void
    {
        $minifier = new HTMLMinifier();
        $minifier->preserveComment('/^\s*keep\s*$/');

        $result1 = $minifier->run('<div><!-- keep --><!-- drop --></div>');
        $result2 = $minifier->run('<p><!-- keep --><!-- drop --></p>');

        $this->assertStringContainsString('<!-- keep -->', $result1);
        $this->assertStringNotContainsString('<!-- drop -->', $result1);
        $this->assertStringContainsString('<!-- keep -->', $result2);
        $this->assertStringNotContainsString('<!-- drop -->', $result2);
    }
}
