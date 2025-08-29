<?php

namespace FluxErp\Traits;

use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;
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
    public static array|int|null $categoryIds = null;

    private static ?array $columnListing = null;

    public static function bootCategorizable(): void
    {
        static::saving(function (Model $model): void {
            // before saving remove virtual attributes
            $model->sanitize();
        });

        static::saved(function (Model $model): void {
            // after saving attach the attributes
            if (! is_null(static::$categoryIds)) {
                $model->categories()->sync(static::$categoryIds);
            }
        });
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')
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
                : static::$categoryIds = $value
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

        if (in_array(HasAdditionalColumns::class, class_uses_recursive($this))) {
            $unguarded = array_merge($unguarded, $this->getAdditionalColumns()->pluck('name')->toArray());
        }

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
                    app(ModelExists::class, ['model' => Category::class]),
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

        self::$categoryIds = $validator->validated();
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
