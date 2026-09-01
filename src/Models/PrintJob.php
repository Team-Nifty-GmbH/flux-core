<?php

namespace FluxErp\Models;

use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Enums\PrintJobStatusEnum;
use FluxErp\Traits\Model\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends FluxModel implements HasMediaForeignKey
{
    use Filterable;

    // Public static methods
    public static function mediaReplaced(int|string|null $oldMediaId, int|string|null $newMediaId): void
    {
        static::query()
            ->where('media_id', $oldMediaId)
            ->update(['media_id' => $newMediaId]);
    }

    protected function casts(): array
    {
        return [
            'status' => PrintJobStatusEnum::class,
            'printed_at' => 'datetime',
            'is_completed' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * @return BelongsTo<Printer, $this>
     */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Protected methods
    protected function broadcastToEveryone(): bool
    {
        return true;
    }
}
