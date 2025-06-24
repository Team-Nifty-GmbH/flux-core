<?php

namespace FluxErp\Contracts;

interface HasMediaForeignKey
{
    public static function mediaReplaced(int|string|null $oldMediaId, int|string|null $newMediaId): void;
}
