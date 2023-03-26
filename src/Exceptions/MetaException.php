<?php

namespace FluxErp\Exceptions;

use Exception;

final class MetaException extends Exception
{
    public static function invalidKey(string $key): self
    {
        return new static("Meta key `{$key}` is not a valid key.");
    }

    public static function modelAttribute(string $key): self
    {
        return new static("Meta key `{$key}` seems to be a model attribute. You must explicitly allow this attribute via `\$metaKeys`.");
    }

    public static function notTranslatable(string $key, $model): self
    {
        $translatableAttributes = implode(', ', $model->getTranslatableMeta());

        return new static("Cannot translate attribute `{$key}` as it's not one of the translatable metas: `$translatableAttributes`");
    }
}
