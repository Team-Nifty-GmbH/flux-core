<?php

namespace FluxErp\Traits\Livewire;

trait HasAdditionalColumns
{
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
