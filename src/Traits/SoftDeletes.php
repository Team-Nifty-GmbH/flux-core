<?php

namespace FluxErp\Traits;

use FluxErp\Casts\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;

/**
 * When using this trait you have to ensure that a column for the user who deleted the model exists in the database.
 * If you dont want to track the deleted by user, use the base SoftDeletes trait.
 */
trait SoftDeletes
{
    use BaseSoftDeletes {
        BaseSoftDeletes::initializeSoftDeletes as initializeSoftDeletesBase;
        BaseSoftDeletes::runSoftDelete as runSoftDeleteBase;
        BaseSoftDeletes::restore as restoreBase;
    }

    public function initializeSoftDeletes(): void
    {
        $this->initializeSoftDeletesBase();

        $this->mergeCasts([
            $this->getDeletedByColumn() => MorphTo::class . ':name',
        ]);
    }

    protected function runSoftDelete(): void
    {
        $this->runSoftDeleteBase();
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $query->update([
            $this->getDeletedByColumn() => auth()->user()
                ? auth()->user()->getMorphClass() . ':' . auth()->user()->getKey()
                : null,
        ]);
    }

    public function restore(): bool
    {
        $this->{$this->getDeletedByColumn()} = null;

        return $this->restoreBase();
    }

    public function getDeletedByColumn(): string
    {
        return defined(static::class . '::DELETED_BY') ? static::DELETED_BY : 'deleted_by';
    }

    public function getDeletedBy(): ?Model
    {
        $user = $this->getRawOriginal($this->getDeletedByColumn());

        return $user ? morph_to($user) : null;
    }
}
