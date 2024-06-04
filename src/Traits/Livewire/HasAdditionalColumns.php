<?php

namespace FluxErp\Traits\Livewire;

trait HasAdditionalColumns
{
    use EnsureUsedInLivewire;

    public array $additionalColumns = [];

    public function mountHasAdditionalColumns(): void
    {
        $this->additionalColumns = $this->getAdditionalColumns();
    }

    public function getAdditionalColumns(): array
    {
        return [];
    }
}
