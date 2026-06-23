<?php

namespace FluxErp\Models;

use ArrayAccess;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;
use Spatie\Tags\Tag as BaseTag;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Tag extends BaseTag implements InteractsWithDataTables
{
    use HasPackageFactory, ResolvesRelationsThroughContainer, Searchable;

    public array $translatable = [];

    // Public static methods
    public static function bootHasSlug(): void
    {
        static::saving(function (Tag $model): void {
            $model->slug = Str::slug($model->name);
        });
    }

    /**
     * Flux tags are not translatable ({@see static::$translatable} is empty), so names are
     * plain strings. spatie/laravel-tags >= 4.12 creates tags with a locale-keyed name array
     * ('name' => [$locale => $value]), which bypasses {@see static::findOrCreateFromString()}
     * and breaks the slug generation in {@see static::bootHasSlug()} and the plain-string
     * lookups in {@see static::findFromString()}. Keep the plain-string behaviour.
     */
    public static function findOrCreate(
        string|array|ArrayAccess $values,
        ?string $type = null,
        ?string $locale = null,
    ): BaseCollection|BaseTag|static {
        $tags = collect($values)->map(
            fn (mixed $value) => $value instanceof BaseTag
                ? $value
                : static::findOrCreateFromString((string) $value, $type, $locale)
        );

        return is_string($values) ? $tags->first() : $tags;
    }

    public static function findFromString(string $name, ?string $type = null, ?string $locale = null): ?static
    {
        return static::query()
            ->where('type', $type)
            ->where(function (Builder $query) use ($name): void {
                $query->where('name', $name)
                    ->orWhere('slug', $name);
            })
            ->first();
    }

    public static function findFromStringOfAnyType(string $name, ?string $locale = null): Collection
    {
        return static::query()
            ->where(function (Builder $query) use ($name): void {
                $query->where('name', $name)
                    ->orWhere('slug', $name);
            })
            ->get();
    }

    public static function findOrCreateFromString(
        string $name,
        ?string $type = null,
        ?string $locale = null
    ): static {
        $locale = $locale ?? static::getLocale();

        $tag = static::findFromString($name, $type, $locale);

        if (! $tag) {
            $tag = static::create([
                'name' => $name,
                'type' => $type,
            ]);
        }

        return $tag;
    }

    // Public methods
    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->slug;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    // Scopes
    public function scopeContaining(Builder $query, string $name, $locale = null): Builder
    {
        return $query->whereRaw(
            'lower(' . $this->getQuery()->getGrammar()->wrap('name') . ') like ?',
            ['%' . mb_strtolower($name) . '%']
        );
    }
}
