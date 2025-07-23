<?php

namespace FluxErp\Traits\Livewire;

use Livewire\Attributes\Url;

trait SupportsGrouping
{
    use EnsureUsedInLivewire;

    public function deleteGroup(string $groupName): void
    {
        auth()
            ->user()
            ->widgets()
            ->where('dashboard_component', static::class)
            ->where('group', $groupName)
            ->delete();

        $this->group = null;
        $this->widgets();
        $this->dispatch('remove-group', groupName: $groupName);
        $this->updatedGroup();
    }

    public function mountSupportsGrouping(): void
    {
        if (
            auth()
                ->user()
                ->widgets()
                ->where('dashboard_component', static::class)
                ->where('group', $this->group)
                ->doesntExist()
        ) {
            $this->group = null;
        }
    }

    public function updatedGroup(): void
    {
        $this->dispatch('gridstack-reinit');
    }
}
