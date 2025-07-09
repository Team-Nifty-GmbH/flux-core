<?php

namespace FluxErp\Models;

class PrintLayout extends FluxModel
{
    public function casts(): array
    {
        return [
            'margin' => 'array',
            'header' => 'array',
            'footer' => 'array',
            'first_page_header' => 'array',
        ];
    }
}
