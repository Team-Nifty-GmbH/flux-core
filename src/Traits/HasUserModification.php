<?php

namespace FluxErp\Traits;

use FluxErp\Casts\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin HasTimestamps
 */
trait HasUserModification
{
    public function getCreatedBy(): ?Model
    {
        $user = $this->getRawOriginal($this->getCreatedByColumn());

        return $user ? morph_to($user) : null;
    }

    public function getCreatedByColumn(): string
    {
        return defined(static::class . '::CREATED_BY') ? static::CREATED_BY : 'created_by';
    }

    public function getUpdatedBy(): ?Model
    {
        $user = $this->getRawOriginal($this->getUpdatedByColumn());

        return $user ? morph_to($user) : null;
    }

    public function getUpdatedByColumn(): string
    {
        return defined(static::class . '::UPDATED_BY') ? static::UPDATED_BY : 'updated_by';
    }

    public function initializeHasUserModification(): void
    {
        $this->mergeCasts([
            $this->getCreatedByColumn() => MorphTo::class . ':name',
            $this->getUpdatedByColumn() => MorphTo::class . ':name',
        ]);
    }

    public function setCreatedAt($value): static
    {
        $this->{$this->getCreatedByColumn()} = auth()->user()
            ? auth()->user()->getMorphClass() . ':' . auth()->user()->getKey()
            : null;

        return parent::setCreatedAt($value);
    }

    public function setUpdatedAt($value): static
    {
        $this->{$this->getUpdatedByColumn()} = auth()->user()
            ? auth()->user()->getMorphClass() . ':' . auth()->user()->getKey()
            : null;

        return parent::setUpdatedAt($value);
    }
}
