<?php

namespace FluxErp\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Printable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\View\Printing\SepaMandate\SepaMandateView;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;

class SepaMandate extends FluxModel implements HasMedia, OffersPrinting
{
    use Communicatable, Filterable, HasClientAssignment, HasPackageFactory, HasSerialNumberRange, HasUserModification,
        HasUuid, InteractsWithMedia, LogsActivity, Printable, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (SepaMandate $mandate): void {
            // reset to original
            if ($mandate->wasChanged('mandate_reference_number')) {
                $mandate->mandate_reference_number = $mandate->getOriginal('mandate_reference_number');
            }

            if (! $mandate->exists && ! $mandate->mandate_reference_number) {
                $mandate->getSerialNumber('mandate_reference_number');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function contactBankConnection(): BelongsTo
    {
        return $this->belongsTo(ContactBankConnection::class);
    }

    public function getPrintViews(): array
    {
        return [
            'sepa-mandate' => SepaMandateView::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signed_mandate')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/svg+xml'])
            ->singleFile();
    }
}
