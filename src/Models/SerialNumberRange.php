<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class SerialNumberRange extends FluxModel
{
    use Filterable, HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    public static function hasPermission(): bool
    {
        return false;
    }

    protected static function booted(): void
    {
        static::creating(function (SerialNumberRange $serialNumberRange): void {
            $serialNumberRange->unique_key = implode('.', [
                $serialNumberRange->model_type,
                $serialNumberRange->model_id,
                $serialNumberRange->type,
                $serialNumberRange->client_id,
            ]);
        });
    }

    protected function casts(): array
    {
        return [
            'has_serial_number' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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

    public function model(): MorphTo
    {
        return $this->morphTo('model');
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

        $modelAttributes = array_fill_keys(
            ModelInfo::forModel(morphed_model($this->model_type))->attributes->pluck('name')->toArray(),
            null
        );

        $modelRecordAttributes = $this->model()->first()?->toArray() ?? [];

        return array_filter(
            Arr::dot(array_merge($defaultAttributes, $modelAttributes, $modelRecordAttributes)),
            fn ($value) => is_string($value)
        );
    }
}
