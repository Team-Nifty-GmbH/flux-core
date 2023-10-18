<?php

namespace FluxErp\Models;

use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormBuilderCollection extends Model
{
    // use HasUpdates;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'values' => 'collection',
    ];

    public function getValuesListAttribute(): ?string
    {
        $allValues = collect($this->values);

        if ($allValues->isNotEmpty()) {
            return $allValues
                ->take(5)
                ->map(function ($item) {
                    return $item['itemValue'] ?? null;
                })
                ->join(',');
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
