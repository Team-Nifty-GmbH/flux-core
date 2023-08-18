<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

class Presentation extends Model
{
    use Commentable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification, HasUuid,
        Searchable, SoftDeletes;

    protected $appends = [
        'url_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public array $translatable = [
        'name',
        'notice',
    ];

    public function urlPublic(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_public ?
                route('presentation.public-html-show', ['uuid' => $this->uuid]) :
                null,
        );
    }

    public function printData(): MorphMany
    {
        return $this->morphMany(PrintData::class, 'model')->orderBy('sort');
    }

    #[SearchUsingPrefix(['id'])]
    #[SearchUsingFullText(['name', 'notice', 'created_at', 'updated_at'])]
    public function toSearchableArray(): array
    {
        return array_diff_key($this->toArray(), array_flip([
            'model_type',
            'model_id',
            'is_public',
        ]));
    }
}
