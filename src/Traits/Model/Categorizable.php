<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;
use FluxErp\Support\VariantInheritance\PivotInheritanceSync;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

trait Categorizable
{
    private static ?array $columnListing = null;

    protected array|int|null $pendingCategoryIds = null;

    public static function bootCategorizable(): void
    {
        static::saving(function (Model $model): void {
            // before saving remove virtual attributes
            $model->sanitize();
        });

        static::saved(function (Model $model): void {
            // after saving attach the attributes
            if (! is_null($model->pendingCategoryIds)) {
                $ids = (array) $model->pendingCategoryIds;

                // A variant (only Product currently has isVariant()/inheritanceEnabled())
                // setting its own category must take ownership (is_inherited = false)
                // and must not have its inherited copies silently dropped just because
                // they're absent from this payload; PivotInheritanceSync::syncOwned()
                // is a no-op passthrough to plain sync() for every other model.
                $takesOwnership = method_exists($model, 'isVariant') && method_exists($model, 'inheritanceEnabled')
                    && $model->isVariant() && $model->inheritanceEnabled();

                resolve_static(PivotInheritanceSync::class, 'syncOwned', [
                    'relation' => $model->categories(),
                    'desired' => collect($ids)->mapWithKeys(fn (int|string $id) => [$id => []])->all(),
                    'takesOwnership' => $takesOwnership,
                ]);

                $model->pendingCategoryIds = null;
            }
        });
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable', 'categorizable')
            ->using(\FluxErp\Models\Pivots\Categorizable::class);
    }

    public function category(): MorphToMany|BelongsTo
    {
        return $this->hasCategoryIdAttribute() ? $this->belongsTo(Category::class) : $this->categories();
    }

    /**
     * returns the first category id if only one category is assigned
     * if set is used the category id is set into a static variable
     */
    public function categoryId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->hasCategoryIdAttribute()
                ? $value
                : ($this->categories()->count() === 1 ? $this->categories()->first()?->id : null),
            set: fn ($value) => $this->hasCategoryIdAttribute()
                ? $value
                : tap($value, fn ($v) => $this->pendingCategoryIds = $v)
        );
    }

    /**
     * merge virtual attributes into fillable, otherwise it would not
     * trigger the setAttribute method when using fill()
     */
    public function initializeCategorizable(): void
    {
        $unguarded = array_diff(
            static::$columnListing ??= Cache::remember(
                'column-listing:' . $this->getTable(),
                86400,
                fn () => Schema::getColumnListing($this->getTable()),
            ),
            $this->getGuarded()
        );

        $this->mergeFillable(array_merge($unguarded, ['category_id', 'categories']));
    }

    /**
     * intercept the set method on the categories model.
     * this saves the validated categories for later in a static variable
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setCategoriesAttribute(?array $value = null): void
    {
        if (is_null($value)) {
            return;
        }

        if (! empty($value) && array_is_list($value) && is_array($value[0])) {
            $value = Arr::pluck($value, 'id');
        }

        $validator = Validator::make($value,
            [
                '*' => [
                    'integer',
                    new ModelExists(Category::class),
                ],
            ],
            [
                'integer' => __('The category :input is no id.'),
                'exists' => __('The category :input is invalid.'),
            ]
        );

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        $this->pendingCategoryIds = $validator->validated();
    }

    /**
     * clears all »virtual« attributes from the model,
     * otherwise the sql query would try to set a non existing field
     * resulting in an exception.
     */
    protected function sanitize(): void
    {
        if ($this->hasCategoryIdAttribute()) {
            return;
        }

        $attributes = $this->getAttributes();
        unset($attributes['category_id']);
        $this->setRawAttributes($attributes);
    }

    private function hasCategoryIdAttribute(): bool
    {
        return in_array('category_id', static::$columnListing ?? []);
    }
}
