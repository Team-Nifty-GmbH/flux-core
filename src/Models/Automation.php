<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'actions' => 'array',
        'payload' => 'array',
        'conditions' => 'array',
    ];
}
