<?php

namespace FluxErp\Models;

use FluxErp\Traits\CascadeSoftDeletes;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class MediaFolder extends FluxModel implements HasMedia
{
    use CascadeSoftDeletes, HasParentChildRelations, HasUserModification, HasUuid, InteractsWithMedia;

    protected static function booted(): void
    {
        static::creating(function (MediaFolder $model): void {
            $model->slug ??= Str::snake(str_replace('.', '_', $model->name));
        });

        static::created(function (MediaFolder $model): void {
            $model->slug = implode('.',
                array_filter([
                    $model->parent?->slug,
                    Str::snake(str_replace('.', '_', $model->name)) . '|' . $model->getKey(),
                ])
            );

            $model->saveQuietly();
        });

        static::updating(function (MediaFolder $model): void {
            if ($model->isDirty('name')) {
                $model->slug = implode('.',
                    array_filter([
                        $model->parent?->slug,
                        Str::snake(str_replace('.', '_', $model->name)) . '|' . $model->getKey(),
                    ])
                );
            }
        });

        static::saved(function (MediaFolder $model): void {
            if ($model->wasChanged('slug')) {
                foreach ($model->children ?? [] as $child) {
                    $child->update([
                        'slug' => $model->slug . '.' . Str::afterLast($child->slug, '.'),
                    ]);
                }
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
}
