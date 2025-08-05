<?php

namespace FluxErp\Models;

use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Traits\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends FluxModel implements HasMediaForeignKey
{
    use Filterable;

    public static function mediaReplaced(int|string|null $oldMediaId, int|string|null $newMediaId): void
    {
        static::query()
            ->where('media_id', $oldMediaId)
            ->update(['media_id' => $newMediaId]);
    }

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
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
