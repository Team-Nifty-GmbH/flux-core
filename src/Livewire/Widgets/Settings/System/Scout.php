<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Actions\Console\RunCommand;
use FluxErp\Console\Commands\Scout\FlushCommand;
use FluxErp\Console\Commands\Scout\ImportCommand;
use FluxErp\Console\Commands\Scout\IndexCommand;
use FluxErp\Console\Commands\Scout\SyncIndexSettingsCommand;
use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Laravel\Scout\Console\DeleteAllIndexesCommand;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Scout extends Component
{
    use Actions, Widgetable;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 5;
    }

    public static function getDefaultOrderRow(): int
    {
        return 1;
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.settings.system.scout');
    }

    #[Renderless]
    public function deleteAllIndexes(): void
    {
        $this->runCommand(DeleteAllIndexesCommand::class);
    }

    #[Renderless]
    public function flush(): void
    {
        $this->runCommand(FlushCommand::class);
    }

    #[Renderless]
    public function import(): void
    {
        $this->runCommand(ImportCommand::class);
    }

    #[Renderless]
    public function index(): void
    {
        $this->runCommand(IndexCommand::class);
    }

    #[Renderless]
    public function syncIndexSettings(): void
    {
        $this->runCommand(SyncIndexSettingsCommand::class);
    }

    protected function runCommand(string $command): void
    {
        try {
            RunCommand::make([
                'command' => $command,
            ])
                ->checkPermission()
                ->validate()
                ->executeAsync();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }
}
