<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\CascadeSoftDeletes;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class MediaFolder extends FluxModel implements HasMedia
{
    use CascadeSoftDeletes, HasParentChildRelations, HasUserModification, HasUuid, InteractsWithMedia;

    protected static function booted(): void
    {
        static::saving(function (MediaFolder $model): void {
            if ($model->parent_id) {
                $model->collection_name = null;
            }
        });

        static::creating(function (MediaFolder $model): void {
            $model->slug ??= Str::of($model->name)
                ->replace('.', '_')
                ->snake()
                ->toString();
        });

        static::created(function (MediaFolder $model): void {
            $model->slug = $model->buildSlug();

            $model->saveQuietly();
        });

        static::updating(function (MediaFolder $model): void {
            if ($model->isDirty(['parent_id', 'collection_name', 'name'])) {
                $model->slug = $model->buildSlug();
            }
        });

        static::saved(function (MediaFolder $model): void {
            if ($model->wasChanged(['parent_id', 'slug'])) {
                $original = $model->getRawOriginal('slug');
                $quotedSlug = $model->getConnection()->getPdo()->quote($model->slug);

                if ($quotedSlug === false) {
                    $quotedSlug = '\'' . addslashes($model->slug) . '\'';
                }

                $model->getAllDescendantsQuery()
                    ->update([
                        'slug' => DB::raw('CONCAT(' . $quotedSlug
                            . ', SUBSTRING(slug, ' . (strlen($original) + 1) . '))'
                        ),
                    ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'mime_types' => 'array',
            'is_readonly' => 'boolean',
        ];
    }

    public function buildSlug(): string
    {
        return implode('.',
            array_filter([
                $this->parent?->slug ?? $this->collection_name,
                Str::of($this->name)
                    ->replace('.', '_')
                    ->snake()
                    ->append('|' . $this->getKey())
                    ->toString(),
            ])
        );
    }
}
