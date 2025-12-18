<?php

namespace FluxErp\Traits\Livewire;

use Livewire\Attributes\Renderless;
use TallStackUi\Foundation\Interactions\Toast;
use TallStackUi\Traits\Interactions as BaseActions;

trait Actions
{
    use BaseActions;

    public function notification(): Toast
    {
        return $this->toast();
    }

    #[Renderless]
    public function modalOpen(string $id): void
    {
        $this->js(<<<JS
            \$modalOpen('$id');
        JS);
    }

    #[Renderless]
    public function modalClose(string $id): void
    {
        $this->js(<<<JS
            \$modalClose('$id');
        JS);
    }
}
