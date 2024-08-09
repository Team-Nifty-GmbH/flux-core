<?php

namespace FluxErp\Traits;

use FluxErp\Casts\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;

trait SoftDeletes
{
    use BaseSoftDeletes {
        BaseSoftDeletes::initializeSoftDeletes as initializeSoftDeletesBase;
        BaseSoftDeletes::runSoftDelete as runSoftDeleteBase;
        BaseSoftDeletes::restore as restoreBase;
    }

    const DELETED_BY = 'deleted_by';

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

        $this->{$this->getDeletedByColumn()} = auth()->user();
        $this->update([
            $this->getDeletedByColumn() => auth()->user(),
        ]);
    }

    public function restore(): bool
    {
        $this->{$this->getDeletedByColumn()} = null;

        return $this->restoreBase();
    }

    public function getDeletedByColumn(): string
    {
        return static::DELETED_BY;
    }
}
