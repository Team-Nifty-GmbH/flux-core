<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Commission extends Model implements InteractsWithDataTables
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'commission_rate' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLabel(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getUrl(): ?string
    {
        return route('accounting.commissions');
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}
