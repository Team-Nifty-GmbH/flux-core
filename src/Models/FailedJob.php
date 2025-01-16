<?php

namespace FluxErp\Models;

class FailedJob extends FluxModel
{
    protected $guarded = [
        'id',
        'uuid',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'failed_at' => 'datetime',
        ];
    }
}
