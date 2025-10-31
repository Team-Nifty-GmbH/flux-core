<?php

namespace FluxErp\Models;

use FluxErp\Traits\CascadeSoftDeletes;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class MediaFolder extends FluxModel implements HasMedia
{
    use CascadeSoftDeletes, HasParentChildRelations, HasUserModification, HasUuid, InteractsWithMedia;

    protected static function booted(): void
    {
        static::creating(function (MediaFolder $model): void {
            $model->slug ??= Str::of($model->name)
                ->replace('.', '_')
                ->snake()
                ->toString();
        });

        static::created(function (MediaFolder $model): void {
            $model->slug = implode('.',
                array_filter([
                    $model->parent?->slug,
                    Str::of($model->name)
                        ->replace('.', '_')
                        ->snake()
                        ->append('|' . $model->getKey())
                        ->toString(),
                ])
            );

            $model->saveQuietly();
        });

        static::updating(function (MediaFolder $model): void {
            if ($model->isDirty(['parent_id', 'name'])) {
                $model->slug = implode('.',
                    array_filter([
                        $model->parent?->slug,
                        Str::of($model->name)
                            ->replace('.', '_')
                            ->snake()
                            ->append('|' . $model->getKey())
                            ->toString(),
                    ])
                );
            }
        });

        static::saved(function (MediaFolder $model): void {
            if ($model->wasChanged(['parent_id', 'slug'])) {
                $original = $model->getRawOriginal('slug');
                $replace = $model->slug;
                $model->getAllDescendantsQuery()
                    ->update([
                        'slug' => DB::raw('CONCAT(\'' . $replace
                            . '\', SUBSTRING(slug, ' . strlen($original) + 1 . '))'
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
}
