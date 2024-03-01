<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class SerialNumberRange extends Model
{
    use Filterable, HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
        'has_serial_number' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::creating(function (SerialNumberRange $serialNumberRange) {
            $serialNumberRange->unique_key = implode('.', [
                $serialNumberRange->model_type,
                $serialNumberRange->model_id,
                $serialNumberRange->type,
                $serialNumberRange->client_id,
            ]);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function getCurrentStyled(): array|string|Translator|Application|null
    {
        return __(
            $this->prefix .
            str_pad((string) $this->current_number, $this->length ?? 0, '0', STR_PAD_LEFT) .
            $this->suffix,
            $this->variables()
        );
    }

    private function variables(): array
    {
        $defaultAttributes = [
            'current_day' => date('d'),
            'current_month' => date('m'),
            'current_year' => date('Y'),
        ];

        if (! $this->model_type) {
            return $defaultAttributes;
        }
        dd($this->model_type, Relation::getMorphedModel($this->model_type));

        $modelAttributes = array_fill_keys(
            ModelInfo::forModel(Relation::getMorphedModel($this->model_type))->attributes->pluck('name')->toArray(),
            null
        );

        $modelRecordAttributes = $this->model()->first()?->toArray() ?? [];

        return array_filter(
            Arr::dot(array_merge($defaultAttributes, $modelAttributes, $modelRecordAttributes)),
            fn ($value) => is_string($value)
        );
    }
}
