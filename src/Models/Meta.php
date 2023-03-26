<?php

namespace FluxErp\Models;

use FluxErp\DataType\Registry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Meta extends Model
{
    use HasTimestamps;

    protected $guarded = [
        'id',
        'metable_type',
        'metable_id',
        'type',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'id_aggregate',
        'published_at_aggregate',
        'key_aggregate',
    ];

    protected $table = 'meta';

    protected $cachedValue;

    protected ?string $forceType = null;

    public function additionalColumn(): BelongsTo
    {
        return $this->belongsTo(AdditionalColumn::class);
    }

    /**
     * Metable Relation.
     */
    public function metable(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Set forced type to be used.
     *
     * @param  ?string  $value
     */
    public function forceType(?string $value): self
    {
        $this->forceType = $value;

        return $this;
    }

    /**
     * Accessor for value.
     *
     * Will unserialize the value before returning it.
     *
     * Successive access will be loaded from cache.
     *
     *
     * @throws \FluxErp\Exceptions\DataTypeException
     */
    public function getValueAttribute(): mixed
    {
        return $this->cachedValue ??= $this->getDataTypeRegistry()
            ->getHandlerForType($this->attributes['type'] ?: 'string')
            ->unserializeValue($this->attributes['value']);
    }

    /**
     * Mutator for value.
     *
     * The `type` attribute will be automatically updated to match the datatype of the input.
     *
     *
     * @throws \FluxErp\Exceptions\DataTypeException
     */
    public function setValueAttribute(mixed $value): void
    {
        $registry = $this->getDataTypeRegistry();

        $this->attributes['type'] = $this->forceType ?? $registry->getTypeForValue($value);

        $this->attributes['value'] = is_null($value)
            ? $value
            : $registry->getHandlerForType($this->attributes['type'])->serializeValue($value);

        $this->cachedValue = null;
    }

    /**
     * Retrieve the underlying serialized value.
     *
     * @return ?string
     */
    public function getRawValueAttribute(): ?string
    {
        return $this->attributes['value'] ?? null;
    }

    /**
     * Load the datatype Registry from the container.
     */
    public static function getDataTypeRegistry(): Registry
    {
        return app('datatype.registry');
    }

    /**
     * Query records where value is considered empty.
     */
    public function scopeWhereValueEmpty(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNull('value')->orWhere('value', '=', ''));
    }

    /**
     * Query records where value is considered not empty.
     */
    public function scopeWhereValueNotEmpty(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNotNull('value')->where('value', '!=', ''));
    }

    /**
     * Query records where value equals the serialized version of the given value.
     * If `$type` is omited the type will be taken from the data type registry.
     *
     * @param  ?string  $type
     */
    public function scopeWhereValue(Builder $query, mixed $value, mixed $operator = '=', ?string $type = null): void
    {
        $registry = $this->getDataTypeRegistry();

        $type ??= $registry->getTypeForValue($value);

        $serializedValue = is_null($value)
            ? $value
            : $registry->getHandlerForType($type)->serializeValue($value);

        $query->where('type', $type)->where('value', $operator, $serializedValue);
    }

    /**
     * Query records where value equals the serialized version of one of the given values.
     * If `$type` is omited the type will be taken from the data type registry.
     *
     * @param  Builder<Meta>  $query
     * @param  ?string  $type
     */
    public function scopeWhereValueIn(Builder $query, array $values, ?string $type = null): void
    {
        $registry = $this->getDataTypeRegistry();

        $serializedValues = collect($values)->map(function ($value) use ($registry, $type) {
            $type = $type ?? $registry->getTypeForValue($value);

            return [
                'type' => $type,
                'value' => $registry->getHandlerForType($type)->serializeValue($value),
            ];
        });

        $query->where(function ($query) use ($serializedValues) {
            $serializedValues->groupBy('type')->each(function ($values, $type) use ($query) {
                $query->orWhere(fn ($q) => $q->where('type', $type)->whereIn('value', $values->pluck('value')));
            });
        });
    }
}
