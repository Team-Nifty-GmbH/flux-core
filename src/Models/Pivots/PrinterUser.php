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

    protected static function booted(): void
    {
        static::saving(function (PrinterUser $printerUser): void {
            if ($printerUser->isDirty('is_default') && $printerUser->is_default) {
                $printerUser->user->printerUsers()
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
