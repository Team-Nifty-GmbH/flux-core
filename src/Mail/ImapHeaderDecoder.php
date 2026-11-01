<?php

namespace FluxErp\Mail;

use Webklex\PHPIMAP\Decoder\HeaderDecoder;

/**
 * Without ext-imap the parent neither decodes encoded words, leaving "=?UTF-8?Q?...?=" in
 * names and file names, nor treats a header word as a plain string: property_exists() in
 * its getEncoding() autoloads the word as a class, and a Laravel alias of that name with a
 * missing target throws, which fails the whole page of messages. Behaves the same with
 * and without the extension, as php-zts has none.
 */
class ImapHeaderDecoder extends HeaderDecoder
{
    public function getEncoding(object|string $structure): string
    {
        if (is_string($structure)) {
            return mb_detect_encoding($structure) ?: $this->fallback_encoding;
        }

        return parent::getEncoding($structure);
    }

    public function mimeHeaderDecode(string $text): array
    {
        if (! str_contains($text, '=?')) {
            return [(object) ['charset' => $this->getEncoding($text), 'text' => $text]];
        }

        $decoded = iconv_mime_decode($text, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        return [(object) ['charset' => 'UTF-8', 'text' => $decoded === false ? $text : $decoded]];
    }
}
