<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Printer;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrinterUser extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'printer_user';

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
