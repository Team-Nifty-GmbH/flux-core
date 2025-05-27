<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Storage extends Component
{
    use Widgetable;

    public ?string $disk_free_space = null;

    public ?string $disk_total_space = null;

    public ?string $view_cache_space = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 2;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public function mount(): void
    {
        $this->getData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.settings.system.storage');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->fill([
            'disk_free_space' => Number::fileSize(disk_free_space('/'), 2),
            'disk_total_space' => Number::fileSize(disk_total_space('/'), 2),
            'view_cache_space' => Number::fileSize(
                array_reduce(
                    glob(storage_path('framework/views/*')),
                    fn ($carry, $item) => $carry + (is_dir($item) ? 0 : filesize($item)),
                    0
                ),
                2
            ),
        ]);
    }
}
