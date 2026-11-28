<?php

namespace FluxErp\Support\Html;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class RichTextSanitizer
{
    public static function sanitize(?string $html): ?string
    {
        if (is_null($html)) {
            return null;
        }

        return strtr(
            (new HtmlSanitizer(
                (new HtmlSanitizerConfig())
                    ->allowSafeElements()
                    ->allowRelativeLinks()
                    ->allowRelativeMedias()
                    ->dropAttribute('id', '*')
                    ->allowAttribute('class', '*')
                    ->allowAttribute('style', '*')
                    ->allowAttribute('target', 'a')
                    ->allowAttribute('rel', 'a')
                    ->allowAttribute('colwidth', ['td', 'th'])
                    ->allowAttribute('data-type', ['a', 'span'])
                    ->allowAttribute('data-id', ['a', 'span'])
                    ->allowAttribute('data-label', ['a', 'span'])
                    ->allowAttribute('data-value', 'span')
                    ->allowAttribute('data-mention-suggestion-char', ['a', 'span'])
                    ->allowAttribute('data-mention-type', ['a', 'span'])
                    ->allowAttribute('data-mention-state', ['a', 'span'])
                    ->withAttributeSanitizer(app(EditorAttributeSanitizer::class))
                    ->withMaxInputLength(-1)
            ))
                ->sanitize($html),
            ['&#43;' => '+', '&#61;' => '=', '&#64;' => '@', '&#96;' => '`']
        );
    }
}
