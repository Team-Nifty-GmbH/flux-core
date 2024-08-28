<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class InterfaceUser extends Authenticatable
{
    use HasApiTokens, HasUserModification, SoftDeletes;

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
