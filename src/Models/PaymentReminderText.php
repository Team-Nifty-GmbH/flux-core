<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PaymentReminderText extends Model
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'mail_to' => 'array',
        'mail_cc' => 'array',
    ];
}