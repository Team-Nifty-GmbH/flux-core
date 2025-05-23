<?php

namespace FluxErp\Traits;

use Closure;
use Exception;
use FluxErp\Casts\MetaAttribute;
use FluxErp\Exceptions\MetaException;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Meta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Translatable\Events\TranslationHasBeenSetEvent;
use Spatie\Translatable\Translatable;

trait HasAdditionalColumns
{
    public static bool $metaDisabled = false;

    protected static ?Collection $additionalColumns = null;

    /**
     * Cache storage for table column names.
     */
    protected static array $metaSchemaColumnsCache = [];

    /**
     * Indicates if all meta assignment is unguarded.
     */
    protected static bool $metaUnguarded = false;

    protected static ?Collection $modelSpecificAdditionalColumns = null;

    /**
     * The allowed meta keys.
     *
     * @var array<string>
     */
    protected array $_metaKeys = ['*'];

    /**
     * Auto-save meta data when model is saved.
     */
    protected bool $autosaveMeta = true;

    /**
     * Cached array of explicitly allowed meta keys.
     *
     * @var array<string>
     */
    protected ?array $explicitlyAllowedMetaKeys = null;

    /**
     * Collection database columns overridden by meta.
     */
    protected ?Collection $fallbackValues = null;

    /**
     * Collection of the changed meta data for this model.
     */
    protected ?Collection $metaChanges = null;

    protected bool $withoutMeta = false;

    private array $translatableMeta = [];

    /**
     * Boot the model trait.
     */
    public static function bootHasAdditionalColumns(): void
    {
        if (static::$metaDisabled) {
            return;
        }

        foreach (
            resolve_static(AdditionalColumn::class, 'query')
                ->whereNotNull('model_id')
                ->where('model_type', app(static::class)->getMorphClass())
                ->get() as $column
        ) {
            static::registerModelSpecificAdditionalColumn($column);
        }

        static::addGlobalScope(function (Builder $builder): void {
            $builder->with(['meta']);
        });

        static::retrieved(function (Model $model): void {
            foreach ($model->getExplicitlyAllowedMetaKeys() as $key) {
                if (array_key_exists($key, $model->attributes)) {
                    $model->setFallbackValue($key, Arr::pull($model->attributes, $key));
                }
            }
        });

        static::saving(function (Model $model): void {
            foreach ($model->getMetaChanges() as $meta) {
                unset($model->{$meta->key});
            }
        });

        static::saved(function (Model $model): void {
            if ($model->autosaveMeta === true) {
                $model->saveMeta();
            }
        });

        static::deleted(function (Model $model): void {
            if (
                $model->autosaveMeta === true
                && ! in_array(SoftDeletes::class, class_uses($model))
            ) {
                $model->purgeMeta();
            }
        });

        if (method_exists(__CLASS__, 'forceDeleted')) {
            static::forceDeleted(function (Model $model): void {
                if ($model->autosaveMeta === true) {
                    $model->purgeMeta();
                }
            });
        }
    }

    /**
     * Determine if meta keys are unguarded
     */
    public static function isMetaUnguarded(): bool
    {
        return static::$metaUnguarded;
    }

    public static function registerModelSpecificAdditionalColumn(Model $additionalColumn): void
    {
        $collection = static::$modelSpecificAdditionalColumns ?? collect();

        $collection->push($additionalColumn);

        static::$modelSpecificAdditionalColumns = $collection;
    }

    /**
     * Re-enable the meta key restrictions.
     */
    public static function reguardMeta(): void
    {
        static::$metaUnguarded = false;
    }

    /**
     * Disable all meta key restrictions.
     */
    public static function unguardMeta(bool $state = true): void
    {
        static::$metaUnguarded = $state;
    }

    protected static function additionalColumnsQuery(): Builder
    {
        return resolve_static(AdditionalColumn::class, 'query')
            ->where('model_type', morph_alias(static::class))
            ->whereNull('model_id');
    }

    public function additionalColumns(): MorphMany
    {
        return $this->morphMany(AdditionalColumn::class, 'model')
            ->setQuery(
                static::additionalColumnsQuery()
                    ->toBase()
                    ->orWhere(function (\Illuminate\Database\Query\Builder $query): void {
                        $query->where('model_type', $this->getMorphClass())
                            ->where('model_id', $this->getKey());
                    })
            );
    }

    public function additionalModelColumns(): MorphMany
    {
        return $this->morphMany(AdditionalColumn::class, 'model');
    }

    /**
     * Enable or disable auto-saving of meta data.
     */
    public function autosaveMeta(bool $enable = true): self
    {
        $this->autosaveMeta = $enable;

        return $this;
    }

    /**
     * Delete the given meta key or keys.
     *
     * @param  string|array<string>  $key
     *
     * @throws MetaException if invalid key is used.
     */
    public function deleteMeta(string|array $key): bool
    {
        DB::beginTransaction();

        $keys = collect(is_array($key) ? $key : [$key]);

        /**
         * If one of the given keys is invalid throw an exception, otherwise delete all
         * meta records for the given keys from the database.
         */
        $deleted = $keys
            ->each(function ($key): void {
                if (! $this->isValidMetaKey($key)) {
                    throw MetaException::invalidKey($key);
                }
            })
            ->filter(fn ($key) => $this->meta()->where('key', $key)->delete());

        DB::commit();

        /**
         * Remove the deleted meta models from the collection of changes
         * and refresh the meta relations to prevent having stale data.
         */
        if ($deleted) {
            $deleted->each(fn ($key) => $this->resetMetaChanges($key));
            $this->refreshMetaRelations();
        }

        /** Check if all given keys could be deleted. */
        return $deleted->count() === $keys->count();
    }

    /**
     * Find current Meta model for the given key.
     */
    public function findMeta(string $key): ?Meta
    {
        if (! $this->exists || ! isset($this->id)) {
            return null;
        }

        return $this->meta?->first(fn ($meta) => $meta->key === $key);
    }

    public function getAdditionalColumnId(string $key): ?int
    {
        return $this->additionalColumns()
            ->where('name', $key)
            ->value('id');
    }

    public function getAdditionalColumns(bool $cached = true): ?Collection
    {
        if ($cached && ! is_null(static::$additionalColumns)) {
            return static::$additionalColumns;
        }

        return $this->setAdditionalColumns()
            ->merge(
                (static::$modelSpecificAdditionalColumns ?? collect())
                    ->where('model_id', $this->getKey())
            );
    }

    /**
     * @return mixed
     *
     * @throws MetaException
     */
    public function getAttribute($key)
    {
        if (! $this->isValidMetaKey($key) || static::$metaDisabled) {
            return parent::getAttribute($key);
        }

        /**
         * If the given key is not explicitly allowed but exists as a real attribute
         * let’s not try to find a meta value for the given key.
         */
        if (
            ! $this->isExplicitlyAllowedMetaKey($key)
            && ($attr = parent::getAttribute($key)) !== null
        ) {
            return $attr;
        }

        /**
         * There seems to be no attribute given and no relation so we either have a key
         * explicitly listed as a meta key or the wildcard (*) was used. Let’s get the meta
         * value for the given key and pipe the result through an accessor if possible.
         * If the value is still `null` check if there is a fallback value which typically
         * means there is an equal named database column which we pulled the value from earlier.
         */
        $value = with($this->getMeta($key), function ($value) use ($key) {
            $accessor = Str::camel('get_' . $key . '_meta');

            if (! method_exists($this, $accessor)) {
                return $value;
            }

            return $this->{$accessor}($value);
        });

        if ($this->isTranslatableMeta($key)) {
            $value = $this->getMetaTranslation($key, app()->getLocale());
        }

        if ($value === null && ! $this->hasMeta($key)) {
            $value = $this->getFallbackValue($key);
        }

        /**
         * Finally delegate back to `parent::getAttribute()` if no meta exists.
         */
        return $value ?? value(
            fn () => ! $this->hasMeta($key) ? parent::getAttribute($key) : null
        );
    }

    /**
     * Get the forced typecast for the given meta key if there is any.
     */
    public function getCastForMetaKey(string $key): ?string
    {
        /** @var ?string $cast */
        $cast = with(
            $this->getMetaKeysProperty(),
            fn ($metaKeys) => $metaKeys[$key] ?? null
        );

        return $cast;
    }

    /**
     * Get the dirty meta collection.
     */
    public function getDirtyMeta(): Collection
    {
        return $this->getMetaChanges();
    }

    /**
     * Get the meta keys explicitly allowed by using `$metaKeys`
     * or by typecasting to `MetaAttribute::class`.
     */
    public function getExplicitlyAllowedMetaKeys(bool $fromCache = true): array
    {
        if ($this->explicitlyAllowedMetaKeys && $fromCache) {
            return $this->explicitlyAllowedMetaKeys;
        }

        return $this->explicitlyAllowedMetaKeys = collect($this->getCasts())
            ->filter(fn ($cast) => $cast === MetaAttribute::class)
            ->keys()
            ->concat($this->getMetaKeys())
            ->filter(fn ($key) => $key !== '*')
            ->unique()
            ->toArray();
    }

    /**
     * Get the fallback value for the given key.
     *
     * @return mixed|null
     */
    public function getFallbackValue(string $key): mixed
    {
        return $this->fallbackValues?->get($key) ?? null;
    }

    /**
     * Get meta value for key.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->findMeta($key)?->value ?? $default;
    }

    /**
     * Get the locally collected meta data.
     */
    public function getMetaChanges(): Collection
    {
        if (! is_null($this->metaChanges)) {
            return $this->metaChanges;
        }

        return $this->resetMetaChanges();
    }

    /**
     * Get the allowed meta keys for the model.
     *
     * @return array<string>
     */
    public function getMetaKeys(): array
    {
        return collect($this->getMetaKeysProperty())->map(
            fn ($value, $key) => is_string($key) ? $key : $value
        )->toArray();
    }

    /**
     * @throws MetaException
     */
    public function getMetaTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        $normalizedLocale = $this->normalizeLocale($key, $locale, $useFallbackLocale);

        $isKeyMissingFromLocale = ($locale !== $normalizedLocale);

        $translations = $this->getMetaTranslations($key);

        $translation = $translations[$normalizedLocale] ?? '';

        $translatableConfig = app(Translatable::class);

        if ($isKeyMissingFromLocale && $translatableConfig->missingKeyCallback) {
            try {
                $callbackReturnValue = (app(Translatable::class)
                    ->missingKeyCallback)($this, $key, $locale, $translation, $normalizedLocale);
                if (is_string($callbackReturnValue)) {
                    $translation = $callbackReturnValue;
                }
            } catch (Exception) {
                // prevent the fallback to crash
            }
        }

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $translation);
        }

        return $translation !== '' ? $translation : null;
    }

    /**
     * @throws MetaException
     */
    public function getMetaTranslations(?string $key = null, ?array $allowedLocales = null): array
    {
        if ($key !== null) {
            $this->guardAgainstNonTranslatableMeta($key);

            $metaValue = json_decode($this->findMeta($key)?->value ?? '', true);

            if (! $metaValue || ! is_array($metaValue) || ! Arr::isAssoc($metaValue)) {
                return [app()->getLocale() => $metaValue];
            }

            return array_filter(
                json_decode($this->findMeta($key)?->value ?? '' ?: '{}', true) ?: [],
                fn ($value, $locale) => $this->filterTranslations($value, $locale, $allowedLocales),
                ARRAY_FILTER_USE_BOTH,
            );
        }

        return array_reduce($this->getTranslatableMeta(), function ($result, $item) use ($allowedLocales) {
            $result[$item] = $this->getMetaTranslations($item, $allowedLocales);

            return $result;
        });
    }

    public function getTranslatableMeta(): array
    {
        return $this->translatableMeta;
    }

    /**
     * @throws MetaException
     * @throws \Spatie\Translatable\Exceptions\AttributeIsNotTranslatable
     */
    public function getTranslatedLocales(string $key): array
    {
        return array_keys($this->isTranslatableMeta($key) ?
            $this->getMetaTranslations($key) : $this->getTranslations($key)
        );
    }

    public function hasAdditionalColumnsValidationRules(): array
    {
        $rules = [];

        if (static::$metaDisabled) {
            return $rules;
        }

        foreach ($this->getAdditionalColumns(false) as $column) {
            $rules[$column->name] ??= $column->validations ?: ['nullable'];

            if ($column->values) {
                $rules[$column->name] = array_merge(
                    Arr::wrap(
                        is_string($rules[$column->name])
                            ? explode('|', $rules[$column->name])
                            : $rules[$column->name]
                    ),
                    [
                        Rule::in(Arr::wrap($column->values)),
                    ]
                );
            }
        }

        return $rules;
    }

    /**
     * Determine if model table has a given column.
     *
     * @param  [string]  $column
     */
    public function hasColumn($column): bool
    {
        $class = get_class($this);

        if (! (static::$metaSchemaColumnsCache[$class] ?? false)) {
            static::$metaSchemaColumnsCache[$class] = collect(
                Cache::remember(
                    'column-listing:' . $this->getTable(),
                    86400,
                    fn () => Schema::getColumnListing($this->getTable()),
                ) ?? []
            )->map(fn ($item) => strtolower($item))->toArray();
        }

        return in_array(strtolower($column), static::$metaSchemaColumnsCache[$class]);
    }

    /**
     * Determine whether the given meta exists.
     */
    public function hasMeta(string $key): bool
    {
        return (bool) $this->findMeta($key);
    }

    /**
     * Initialize the HasMeta trait.
     */
    public function initializeHasAdditionalColumns(): void
    {
        if (static::$metaDisabled) {
            return;
        }

        $this->mergeCasts(
            $this->getAdditionalColumns()?->mapWithKeys(fn (AdditionalColumn $column) => [$column->name => MetaAttribute::class])
                ->toArray() ?? []
        );

        $this->translatableMeta =
            Cache::store('array')->rememberForever(
                'meta_translatable_' . get_class($this),
                fn () => $this->getAdditionalColumns()
                    ->where('is_translatable', '=', true)
                    ->whereNull('values')
                    ->where('field_type', '=', 'text')
                    ->pluck('name')
                    ->toArray()
            );
    }

    /**
     * Determine if the given key was explicitly allowed.
     */
    public function isExplicitlyAllowedMetaKey(string $key): bool
    {
        return in_array($key, $this->getExplicitlyAllowedMetaKeys());
    }

    public function isFillable($key): bool
    {
        return parent::isFillable($key) || (! static::$metaDisabled && $this->isValidMetaKey($key));
    }

    /**
     * Determine if meta is dirty.
     */
    public function isMetaDirty(?string $key = null): bool
    {
        return (bool) with(
            $this->getMetaChanges(),
            fn ($meta) => $key ? $meta->has($key) : $meta->isNotEmpty()
        );
    }

    /**
     * Determine if the meta key wildcard (*) is set.
     */
    public function isMetaWildcardSet(): bool
    {
        return in_array('*', $this->getMetaKeys());
    }

    /**
     * Determine if the given key is an allowed meta key.
     */
    public function isModelAttribute(string $key): bool
    {
        return
            $this->hasSetMutator($key) ||
            $this->hasGetMutator($key) ||
            $this->hasAttributeSetMutator($key) ||
            $this->hasAttributeGetMutator($key) ||
            $this->isEnumCastable($key) ||
            $this->isClassCastable($key) ||
            str_contains($key, '->') ||
            $this->hasColumn($key) ||
            array_key_exists($key, parent::getAttributes());
    }

    public function isTranslatableMeta(string $key): bool
    {
        return in_array($key, $this->translatableMeta);
    }

    /**
     * Determine if the given key is an allowed meta key.
     */
    public function isValidMetaKey(string $key): bool
    {
        if ($this->isMetaUnguarded()) {
            return true;
        }

        if ($this->isExplicitlyAllowedMetaKey($key)) {
            return true;
        }

        if ($this->isModelAttribute($key)) {
            return false;
        }

        return $this->isMetaWildcardSet();
    }

    /**
     * Relationship to all `Meta` models associated with this model.
     */
    public function meta(): MorphMany
    {
        return $this->morphMany(Meta::class, 'model');
    }

    /**
     * Get or set the allowed meta keys for the model.
     */
    public function metaKeys(?array $metaKeys = null): array
    {
        if (! $metaKeys) {
            return $this->getMetaKeysProperty();
        }

        if (property_exists($this, 'metaKeys')) {
            $this->metaKeys = $metaKeys;
        } else {
            $this->_metaKeys = $metaKeys;
        }

        $this->getExplicitlyAllowedMetaKeys(false);

        return $this->getMetaKeysProperty();
    }

    /**
     * Get all meta values as a key => value collection.
     */
    public function pluckMeta(): Collection
    {
        return collect($this->getExplicitlyAllowedMetaKeys())
            ->mapWithKeys(fn ($key) => [$key => null])
            ->merge($this->meta->pluck('value', 'key'));
    }

    /**
     * Delete all meta for the given model.
     */
    public function purgeMeta(): self
    {
        $this->meta()->delete();
        $this->refreshMetaRelations();

        return $this;
    }

    /**
     * Refresh the meta relations.
     */
    public function refreshMetaRelations(): self
    {
        if ($this->relationLoaded('meta')) {
            $this->unsetRelation('meta');
        }

        return $this;
    }

    /**
     * @throws MetaException
     */
    public function relationsToArray(): array
    {
        $array = parent::relationsToArray();

        if (static::$metaDisabled) {
            return $array;
        }

        $meta = $this->meta?->mapWithKeys(
            fn (Meta $meta) => [
                $meta->key => $this->isTranslatableMeta($meta->key) ?
                    $this->getMetaTranslation($meta->key, app()->getLocale()) : $meta->value,
            ]
        )->toArray();
        unset($array['meta']);

        return array_merge($array, $meta ?? []);
    }

    /**
     * Reset the meta changes for the given key.
     */
    public function resetMeta(string $key): Collection
    {
        return $this->resetMetaChanges($key);
    }

    /**
     * Reset the meta changes collection for the given key.
     * Resets the entire collection if nothing is passed.
     */
    public function resetMetaChanges(?string $key = null): Collection
    {
        if ($key && $this->metaChanges) {
            $this->metaChanges->forget($key);

            return $this->metaChanges;
        }

        return $this->metaChanges = collect();
    }

    /**
     * Store the meta data from the Meta Collection.
     * Returns `true` if all meta was saved successfully.
     *
     * @throws MetaException
     */
    public function saveMeta(string|array|null $key = null, mixed $value = null): bool
    {
        /**
         * If we have exactly two arguments set and save the value for the given key.
         */
        if (count(func_get_args()) === 2) {
            $this->setMeta($key, $value);

            return $this->saveMeta($key);
        }

        /**
         * Get all pending meta changes.
         */
        $changes = $this->getMetaChanges();

        /**
         * If no arguments were passed, all changes should be persisted.
         */
        if (empty(func_get_args())) {
            return tap($changes->every(function (Meta $meta, $key) use ($changes) {
                return tap($this->storeMeta($meta), fn ($saved) => $saved && $changes->forget($key));
            }), fn () => $this->refreshMetaRelations());
        }

        /**
         * If only one argument was passed and it’s an array, let’s assume it
         * is a key => value pair that should be stored.
         */
        if (is_array($key)) {
            return collect($key)->every(fn ($value, $name) => $this->saveMeta($name, $value));
        }

        /**
         * Otherwise pull and delete the given key from the array of changes and
         * persist the change. Refresh the relations afterwards to prevent stale data.
         */
        if (! $changes->has($key)) {
            return false;
        }

        $meta = $changes->pull($key);

        return tap((bool) $this->storeMeta($meta), function ($saved): void {
            if ($saved) {
                $this->refreshMetaRelations();
            }
        });
    }

    /**
     * Store the model without saving attached meta data.
     */
    public function saveWithoutMeta(): bool
    {
        $previousSetting = $this->autosaveMeta;

        $this->autosaveMeta = false;

        return tap($this->save(), fn () => $this->autosaveMeta = $previousSetting);
    }

    /**
     * Query records not having meta data for the given key  with "or" where clause.
     * Pass an array to find records not having meta for any of the given keys.
     */
    public function scopeOrWhereDoesntHaveMeta(Builder $query, string|array $key): void
    {
        $query->whereDoesntHaveMeta($key, 'or');
    }

    /**
     * Query records having meta data for the given key with "or" where clause.
     * Pass an array to find records having meta for at least one of the given keys.
     */
    public function scopeOrWhereHasMeta(Builder $query, string|array $key): void
    {
        $query->whereHasMeta($key, 'or');
    }

    /**
     * Query records having meta with a specific key and value with "or" clause.
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     */
    public function scopeOrWhereMeta(
        Builder $query,
        string|Closure $key,
        mixed $operator = null,
        mixed $value = null): void
    {
        $query->whereMeta($key, $operator, $value, 'or');
    }

    /**
     * Query records where meta does not exist or is empty with "or" clause.
     */
    public function scopeOrWhereMetaEmpty(Builder $query, string|array $key): void
    {
        $query->whereMetaEmpty($key, 'or');
    }

    /**
     * Query records having one of the given values for the given key with "or" clause.
     */
    public function scopeOrWhereMetaIn(Builder $query, string $key, array $values): void
    {
        $query->whereMetaIn($key, $values, 'or');
    }

    /**
     * Query records where meta exists and is not empty with "or" clause.
     */
    public function scopeOrWhereMetaNotEmpty(Builder $query, string|array $key): void
    {
        $query->whereMetaNotEmpty($key, 'or');
    }

    /**
     * Query records having meta with a specific value and the given type with "or" clause.
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     */
    public function scopeOrWhereMetaOfType(
        Builder $query,
        string $type,
        string $key,
        mixed $operator,
        mixed $value = null): void
    {
        $query->whereMetaOfType($type, $key, $operator, $value, 'or');
    }

    /**
     * Query records having raw meta with a specific key and value without checking type with "or" clause.
     * Make sure that the supplied $value is a string or string castable.
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     */
    public function scopeOrWhereRawMeta(
        Builder $query,
        string $key,
        mixed $operator,
        mixed $value = null): void
    {
        $query->whereRawMeta($key, $operator, $value, 'or');
    }

    /**
     * Query records not having meta data for the given key.
     * Pass an array to find records not having meta for any of the given keys.
     */
    public function scopeWhereDoesntHaveMeta(Builder $query, string|array $key, string $boolean = 'and'): void
    {
        $keys = is_array($key) ? $key : [$key];
        $method = $boolean === 'or' ? 'orWhereDoesntHave' : 'whereDoesntHave';

        $query->{$method}('meta', function (Builder $query) use ($keys): void {
            $query->whereIn('key', $keys);
        }, '=', count($keys));
    }

    /**
     * Query records having meta data for the given key.
     * Pass an array to find records having meta for at least one of the given keys.
     */
    public function scopeWhereHasMeta(Builder $query, string|array $key, string $boolean = 'and'): void
    {
        $keys = is_array($key) ? $key : [$key];
        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$method}('meta', function (Builder $query) use ($keys): void {
            $query->whereIn('key', $keys);
        });
    }

    /**
     * Query records having meta with a specific key and value.
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     */
    public function scopeWhereMeta(
        Builder $query,
        string|Closure $key,
        mixed $operator = null,
        mixed $value = null,
        string $boolean = 'and'): void
    {
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$method}('meta', function (Builder $query) use ($key, $operator, $value): void {
            $query->when(
                $key instanceof Closure
                    ? $key
                    : fn ($q) => $q->where('meta.key', $key)->whereValue($value, $operator)
            );
        });
    }

    /**
     * Query records where meta does not exist or is empty.
     */
    public function scopeWhereMetaEmpty(Builder $query, string|array $key, string $boolean = 'and'): void
    {
        $keys = is_array($key) ? $key : [$key];

        $query->where(function (Builder $query) use ($keys): void {
            $query->whereDoesntHaveMeta($keys)->orWhereMeta(
                fn (Builder $q) => $q->whereIn('meta.key', $keys)->whereValueEmpty()
            );
        }, null, null, $boolean);
    }

    /**
     * Query records having one of the given values for the given key.
     */
    public function scopeWhereMetaIn(Builder $query, string $key, array $values, string $boolean = 'and'): void
    {
        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$method}('meta', function (Builder $query) use ($key, $values): void {
            $query->where('meta.key', $key)->whereValueIn($values);
        });
    }

    /**
     * Query records where meta exists and is not empty.
     */
    public function scopeWhereMetaNotEmpty(Builder $query, string|array $key, string $boolean = 'and'): void
    {
        $keys = is_array($key) ? $key : [$key];
        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$method}('meta', function (Builder $query) use ($keys): void {
            $query->whereIn('meta.key', $keys)
                ->whereValueNotEmpty();
        }, '=', count($keys));
    }

    /**
     * Query records having meta with a specific value and the given type.
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     */
    public function scopeWhereMetaOfType(
        Builder $query,
        string $type,
        string $key,
        mixed $operator,
        mixed $value = null,
        string $boolean = 'and'): void
    {
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$method}('meta', function (Builder $query) use ($type, $key, $operator, $value): void {
            $query->where('meta.key', $key)->whereValue($value, $operator, $type);
        });
    }

    /**
     * Query records having raw meta with a specific key and value without checking type.
     * Make sure that the supplied $value is a string or string castable.
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     */
    public function scopeWhereRawMeta(
        Builder $query,
        string $key,
        mixed $operator,
        mixed $value = null,
        string $boolean = 'and'): void
    {
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$method}('meta', function (Builder $query) use ($key, $operator, $value): void {
            $query->where('meta.key', $key)->where('value', $operator, $value);
        });
    }

    public function setAdditionalColumns(): mixed
    {
        return static::$additionalColumns = $this->additionalColumns()->get()?->unique('name');
    }

    /**
     * @return Meta|mixed
     *
     * @throws MetaException
     */
    public function setAttribute($key, $value)
    {
        if (static::$metaDisabled || $this->withoutMeta || ! $this->isValidMetaKey($key)) {
            return parent::setAttribute($key, $value);
        }

        return $this->setMetaFromString($key, $value);
    }

    /**
     * Add value to the list of columns overridden by meta.
     */
    public function setFallbackValue(string $key, mixed $value = null): self
    {
        ($this->fallbackValues ??= collect())->put($key, $value);

        return $this;
    }

    /**
     * @throws MetaException if invalid key is used.
     */
    public function setMeta(string|array $key, mixed $value = null): Meta|Collection
    {
        if (is_array($key)) {
            return $this->setMetaFromArray($key);
        }

        return $this->setMetaFromString($key, $value);
    }

    public function setMetaChanges($changes): void
    {
        $this->metaChanges = $changes;
    }

    /**
     * @throws MetaException
     */
    public function setMetaTranslation(string $key, string $locale, $value): Meta
    {
        $this->guardAgainstNonTranslatableMeta($key);

        $translations = $this->getMetaTranslations($key);

        $oldValue = $translations[$locale] ?? '';

        if ($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$key];
        }

        $translations[$locale] = $value;

        $meta = $this->setMetaFromString($key, $this->asJson($translations), true);

        event(new TranslationHasBeenSetEvent($this, $key, $locale, $oldValue, $value));

        return $meta;
    }

    /**
     * @throws MetaException
     */
    public function setMetaTranslations(string $key, array $translations): array
    {
        $this->guardAgainstNonTranslatableMeta($key);

        $meta = [];
        if (! empty($translations)) {
            foreach ($translations as $locale => $translation) {
                $meta[] = $this->setMetaTranslation($key, $locale, $translation);
            }
        } else {
            $meta[] = $this->setMetaFromString($key, $this->asJson([]), true);
        }

        return $meta;
    }

    public function withoutMeta(bool $withoutMeta = true): static
    {
        $this->withoutMeta = $withoutMeta;

        return $this;
    }

    /**
     * Get the value from the $metaKeys property if set or a fallback.
     */
    protected function getMetaKeysProperty(): array
    {
        if (property_exists($this, 'metaKeys') && is_array($this->metaKeys)) {
            return $this->metaKeys;
        }

        return $this->_metaKeys;
    }

    /**
     * @throws MetaException
     */
    protected function guardAgainstNonTranslatableMeta(string $key): void
    {
        if (! $this->isTranslatableMeta($key)) {
            throw MetaException::notTranslatable($key, $this);
        }
    }

    /**
     * Set meta values from array of $key => $value pairs.
     */
    protected function setMetaFromArray(array $metas): Collection
    {
        return collect($metas)->map(function ($value, $key) {
            return $this->setMetaFromString($key, $value);
        });
    }

    /**
     * Add or update the value of the `Meta` at a given string key.
     *
     *
     * @throws MetaException if invalid key is used.
     */
    protected function setMetaFromString(string $key, mixed $value, bool $isTranslated = false): Meta
    {
        /**
         * If one is trying to set a model attribute as meta without explicitly
         * whitelisting the attribute throw an exception.
         */
        if ($this->isModelAttribute($key) && ! $this->isExplicitlyAllowedMetaKey($key)) {
            throw MetaException::modelAttribute($key);
        }

        /**
         * Check if the given key was whitelisted.
         */
        if (! $this->isValidMetaKey($key)) {
            throw MetaException::invalidKey($key);
        }

        /**
         * Get all changed meta from our cache collection.
         */
        $meta = $this->getMetaChanges();

        /**
         * Let’s check if there is a mutator for the given meta key and pipe
         * the given value through it if so.
         */
        $value = with(Str::camel('set_' . $key . '_meta'), function ($mutator) use ($value) {
            if (! method_exists($this, $mutator)) {
                return $value;
            }

            return $this->{$mutator}($value);
        });

        if ($this->isTranslatableMeta($key) && ! $isTranslated) {
            return $this->setMetaTranslation($key, app()->getLocale(), $value);
        }
        $attributes = [
            'value' => $value,
            'additional_column_id' => $this->getAdditionalColumnId($key),
        ];

        if ($model = $this->findMeta($key)) {
            $model
                ->forceType($this->getCastForMetaKey($key))
                ->forceFill($attributes);

            /**
             * If there already is a persisted meta for the given key, let’s check if the
             * given value would result in a dirty model – if not skip here.
             */
            if ($model->isDirty()) {
                $this->setMetaChanges($meta->put($key, $model));

                return $model;
            }

            $model->forceFill($model->getOriginal());
        }

        /**
         * Fill the meta with the given attributes and save the changes in our collection.
         * This will not persist the given meta to the database.
         */
        return $meta[$key] = (new Meta(['key' => $key]))
            ->forceType($this->getCastForMetaKey($key))
            ->forceFill($attributes);
    }

    /**
     * Store a single Meta model.
     *
     * @return Meta|false
     */
    protected function storeMeta(Meta $meta): bool|Meta
    {
        return $this->meta()->save($meta);
    }
}
