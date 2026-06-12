<?php

namespace FluxErp\Livewire\Mobile;

use FluxErp\ShareTargetActions\ShareTargetActionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TallStackUi\Traits\Interactions;

class ShareTarget extends Component
{
    use Interactions;
    use WithFileUploads;

    /** @var TemporaryUploadedFile[] */
    public array $files = [];

    public function render(): View
    {
        return view('flux::livewire.mobile.share-target');
    }

    #[Computed]
    public function actions(): array
    {
        $mimeTypes = array_map(
            fn (TemporaryUploadedFile $file) => $file->getMimeType(),
            $this->files
        );

        return collect(app(ShareTargetActionManager::class)->all())
            ->map(fn (string $action) => [
                'class' => $action,
                'label' => $action::label(),
                'icon' => $action::icon(),
                'enabled' => $this->files
                    && (count($this->files) === 1 || $action::supportsMultiple())
                    && collect($mimeTypes)->every(fn (?string $mimeType) => $action::accepts($mimeType)),
            ])
            ->values()
            ->toArray();
    }

    #[Renderless]
    public function executeAction(string $action): void
    {
        if (! app(ShareTargetActionManager::class)->has($action) || ! $this->files) {
            $this->toast()
                ->error(__('Invalid action.'))
                ->send();

            return;
        }

        try {
            $redirect = app($action)->handle($this->files);
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->dispatch(
            'share-target-completed',
            redirect: $redirect ?? route('dashboard', absolute: false)
        );
    }
}
