<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected function attributeChanges(): Attribute
    {
        return Attribute::get(fn (mixed $value): ?Collection => $this->withoutHiddenSubjectAttributes($value))
            ->withoutObjectCaching();
    }

    protected function properties(): Attribute
    {
        return Attribute::get(fn (mixed $value): ?Collection => $this->withoutHiddenSubjectAttributes($value))
            ->withoutObjectCaching();
    }

    protected function withoutHiddenSubjectAttributes(mixed $value): ?Collection
    {
        if (is_null($value)) {
            return null;
        }

        $changes = is_string($value) ? json_decode($value, true) : collect($value)->toArray();

        if (! is_array($changes)) {
            return collect();
        }

        $subjectClass = $this->subject_type ? morphed_model($this->subject_type) ?? $this->subject_type : null;
        $hidden = $subjectClass && class_exists($subjectClass) ? app($subjectClass)->getHidden() : [];

        foreach (['attributes', 'old'] as $key) {
            if (is_array($changes[$key] ?? null)) {
                $changes[$key] = Arr::except($changes[$key], $hidden);
            }
        }

        return collect($changes);
    }
}
