<?php

namespace FluxErp\Support\MediaLibrary;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

class ContentDisposition
{
    public static function make(string $disposition, string $filename): string
    {
        return HeaderUtils::makeDisposition($disposition, $filename, static::asciiFallback($filename));
    }

    public static function asciiFallback(string $filename): string
    {
        $fallback = preg_replace('/[^\x20-\x7e]|[%\/\\\\]/', '_', Str::ascii($filename));

        return $fallback === '' ? 'file' : $fallback;
    }
}
