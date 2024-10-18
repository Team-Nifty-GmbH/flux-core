<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class InterfaceUser extends FluxAuthenticatable
{
    use HasUserModification, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
    ];

    public function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::make($value)
        );
    }
}
