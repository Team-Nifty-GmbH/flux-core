<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;

class PrintData extends Model implements HasMedia
{
    use Filterable, HasPackageFactory, HasUuid, InteractsWithMedia, Searchable;

    protected $appends = [
        'url_public',
    ];

    protected $casts = [
        'data' => 'object',
        'is_public' => 'boolean',
        'is_template' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'uuid',
        'data',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function urlPublic(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_public ?
                route('print.public-html-show', ['uuid' => $this->uuid]) :
                null,
        );
    }

    #[SearchUsingPrefix(['id'])]
    #[SearchUsingFullText(['template_name', 'created_at', 'updated_at'])]
    public function toSearchableArray(): array
    {
        return array_diff_key($this->toArray(), array_flip([
            'model_type',
            'model_id',
            'view',
            'sort',
            'is_public',
            'is_template',
        ]));
    }
}
