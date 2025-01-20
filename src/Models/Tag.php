<?php

namespace FluxErp\Models;

use FluxErp\Traits\ResolvesRelationsThroughContainer;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Spatie\Tags\Tag as BaseTag;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Tag extends BaseTag implements InteractsWithDataTables
{
    use ResolvesRelationsThroughContainer, Searchable;

    public array $translatable = [];

    public static function bootHasSlug(): void
    {
        static::saving(function (Tag $model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public static function findFromString(string $name, ?string $type = null, ?string $locale = null)
    {
        return static::query()
            ->where('type', $type)
            ->where(function (Builder $query) use ($name) {
                $query->where('name', $name)
                    ->orWhere('slug', $name);
            })
            ->first();
    }

    public static function findFromStringOfAnyType(string $name, ?string $locale = null): Collection
    {
        return static::query()
            ->where('name', $name)
            ->orWhere('slug', $name)
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

    public function scopeContaining(Builder $query, string $name, $locale = null): Builder
    {
        return $query->whereRaw(
            'lower(' . $this->getQuery()->getGrammar()->wrap('name') . ') like ?',
            ['%' . mb_strtolower($name) . '%']
        );
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->slug;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}
