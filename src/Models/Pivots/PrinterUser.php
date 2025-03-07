<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Printer;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrinterUser extends FluxPivot
{
    protected $table = 'printer_user';

    public $timestamps = false;

    public $incrementing = true;

    protected $primaryKey = 'pivot_id';

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
