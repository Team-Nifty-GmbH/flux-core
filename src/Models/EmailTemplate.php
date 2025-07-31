<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Casts\AsHtmlString;
use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class EmailTemplate extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use HasPackageFactory, HasUserModification, HasUuid, InteractsWithMedia, Searchable;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'subject' => AsStringable::class,
            'html_body' => AsHtmlString::class,
            'text_body' => AsStringable::class,
        ];
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return Str::of(
            html_entity_decode(
                $this->subject ?? $this->html_body?->toHtml() ?? $this->text_body ?? ''
            )
        )
            ->stripTags()
            ->limit();
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getTextBodyAttribute($value): ?Stringable
    {
        return $value
            ? Str::of($value)
            : Str::of($this->html_body?->toHtml() ?? '')->stripTags();
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function orderTypes(): HasMany
    {
        return $this->hasMany(OrderType::class);
    }
}
