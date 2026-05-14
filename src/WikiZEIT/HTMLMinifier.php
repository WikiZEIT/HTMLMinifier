<?php

/*
 * This is part of WikiZEIT/html-minifier package
 * Copyright (c) 2026 Jakub T. Jankiewicz <https://jakub.jankiewicz.org>
 * Released under MIT license
 */

namespace WikiZEIT;

use Wikimedia\Minify\JavaScriptMinifier;
use Wikimedia\Minify\CSSMin;

class HTMLMinifier
{
    private array $preservedBlocks = [];
    private int $placeholderIndex = 0;

    /** @var string[] Regex patterns for HTML comments to keep */
    private array $preservedComments = [
        '/^\[if\s/',
    ];

    public static function minify(string $html): string
    {
        return (new self())->run($html);
    }

    public function preserveComment(string $pattern): self
    {
        $this->preservedComments[] = $pattern;
        return $this;
    }

    public function run(string $html): string
    {
        $this->preservedBlocks = [];
        $this->placeholderIndex = 0;

        $html = $this->preserveBlocks($html);
        $html = $this->removeComments($html);
        $html = $this->collapseWhitespace($html);
        $html = $this->restoreBlocks($html);
        return trim($html);
    }

    private function placeholder(): string
    {
        return '<!--MINIFY_PRESERVE_' . ($this->placeholderIndex++) . '-->';
    }

    private function preserveBlocks(string $html): string
    {
        // Preserve <pre>, <code>, <textarea> content verbatim
        $html = preg_replace_callback(
            '#(<\s*(pre|code|textarea)\b[^>]*>)(.*?)(</\s*\2\s*>)#si',
            function ($m) {
                $ph = $this->placeholder();
                $this->preservedBlocks[$ph] = $m[0];
                return $ph;
            },
            $html
        );

        // Extract and minify <script> blocks
        $html = preg_replace_callback(
            '#(<\s*script\b[^>]*>)(.*?)(</\s*script\s*>)#si',
            function ($m) {
                $ph = $this->placeholder();
                $attrs = $m[1];
                if (preg_match('/\btype\s*=\s*["\']application\/(?:ld\+)?json["\']/i', $attrs)) {
                    $minified = $this->minifyJson(trim($m[2]));
                    $this->preservedBlocks[$ph] = $attrs . $minified . $m[3];
                } elseif (preg_match('/\btype\s*=\s*["\'](?!text\/javascript)[^"\']+["\']/i', $attrs)) {
                    $this->preservedBlocks[$ph] = $m[0];
                } else {
                    $minified = $this->minifyJs(trim($m[2]));
                    $this->preservedBlocks[$ph] = $attrs . $minified . $m[3];
                }
                return $ph;
            },
            $html
        );

        // Extract and minify <style> blocks
        $html = preg_replace_callback(
            '#(<\s*style\b[^>]*>)(.*?)(</\s*style\s*>)#si',
            function ($m) {
                $ph = $this->placeholder();
                $minified = $this->minifyCss(trim($m[2]));
                $this->preservedBlocks[$ph] = $m[1] . $minified . $m[3];
                return $ph;
            },
            $html
        );

        return $html;
    }

    private function removeComments(string $html): string
    {
        return preg_replace_callback(
            '/<!--(.*?)-->/s',
            function ($m) {
                if (str_starts_with($m[0], '<!--MINIFY_PRESERVE_')) {
                    return $m[0];
                }
                foreach ($this->preservedComments as $pattern) {
                    if (preg_match($pattern, $m[1])) {
                        return $m[0];
                    }
                }
                return '';
            },
            $html
        );
    }

    private function collapseWhitespace(string $html): string
    {
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        return $html;
    }

    private function restoreBlocks(string $html): string
    {
        foreach (array_reverse($this->preservedBlocks, true) as $ph => $content) {
            $html = str_replace($ph, $content, $html);
        }
        return $html;
    }

    private function minifyJs(string $js): string
    {
        if (trim($js) === '') {
            return '';
        }
        try {
            return JavaScriptMinifier::minify($js);
        } catch (\Exception $e) {
            return $js;
        }
    }

    private function minifyJson(string $json): string
    {
        if (trim($json) === '') {
            return '';
        }
        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                'Invalid JSON in <script> block: ' . json_last_error_msg()
            );
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function minifyCss(string $css): string
    {
        if (trim($css) === '') {
            return '';
        }
        try {
            return CSSMin::minify($css);
        } catch (\Exception $e) {
            return $css;
        }
    }
}
