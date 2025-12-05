<?php

namespace FluxErp\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Traits\Model\Communicatable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasClientAssignment;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasSerialNumberRange;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\Printable;
use FluxErp\Traits\Model\SoftDeletes;
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
            'sepa_mandate_type_enum' => SepaMandateTypeEnum::class,
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

    public function getEmailTemplateModelType(): ?string
    {
        return morph_alias(static::class);
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
