<?php

namespace App\Services\Catalogue;

use DOMDocument;
use DOMElement;
use DOMNode;

class CatalogueHtml
{
    /**
     * @param  callable(string, bool): ?string  $url
     */
    public function clean(string $html, callable $url): string
    {
        if (trim($html) === '') {
            return '';
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $body = $document->getElementsByTagName('body')->item(0);
            if ($body === null) {
                return '';
            }
            $this->walk($body, $url);
            $result = '';
            foreach ($body->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }

            return trim($result);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * @param  callable(string, bool): ?string  $url
     */
    private function walk(DOMNode $parent, callable $url): void
    {
        $allowed = ['p', 'br', 'div', 'span', 'ul', 'ol', 'li', 'strong', 'em', 'b', 'i', 'u', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'a', 'img', 'sup', 'sub', 'hr'];
        $drop = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math', 'link', 'meta', 'base', 'template'];
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE) {
                $parent->removeChild($node);

                continue;
            }
            if (! $node instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($node->tagName);
            if (in_array($tag, $drop, true)) {
                $parent->removeChild($node);

                continue;
            }
            $this->walk($node, $url);
            if (! in_array($tag, $allowed, true)) {
                while ($node->firstChild !== null) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = $attribute->value;
                if (($tag === 'a' && $name === 'href') || ($tag === 'img' && $name === 'src')) {
                    $resolved = $url($value, $tag === 'img');
                    if ($resolved === null) {
                        $node->removeAttribute($name);
                    } else {
                        $node->setAttribute($name, $resolved);
                    }
                } elseif ($tag === 'ol' && $name === 'start' && ctype_digit($value) && (int) $value > 0 && (int) $value <= 1000) {
                    continue;
                } elseif (($name === 'alt' && $tag === 'img') || $name === 'title') {
                    continue;
                } elseif (in_array($name, ['colspan', 'rowspan'], true) && in_array($tag, ['td', 'th'], true) && ctype_digit($value) && (int) $value > 0 && (int) $value <= 100) {
                    continue;
                } else {
                    $node->removeAttribute($attribute->name);
                }
            }
            if ($tag === 'img' && ! $node->hasAttribute('src')) {
                $parent->removeChild($node);
            }
        }
    }
}
