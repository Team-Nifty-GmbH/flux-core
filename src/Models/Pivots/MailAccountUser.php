<?php

namespace FluxErp\Models\Pivots;

class MailAccountUser extends FluxPivot
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}
