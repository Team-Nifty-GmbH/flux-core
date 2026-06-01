<?php

namespace FluxErp\Contracts;

interface MentionsContent
{
    /**
     * @return array<int, string>
     */
    public function mentionableTextFields(): array;

    public function mentionScannableText(): string;
}
