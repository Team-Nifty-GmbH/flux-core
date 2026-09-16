<?php

namespace FluxErp\Contracts;

interface MentionsContent
{
    /**
     * @return array<int, string>
     */
    public function mentionableColumns(): array;

    public function mentionScannableText(): string;
}
