<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class SettingsComponent extends Component
{
    use Actions;

    abstract protected function getFormPropertyName(): string;

    public function mount(): void
    {
        $this->{$this->getFormPropertyName()}
            ->fill(app($this->{$this->getFormPropertyName()}->getSettingsClass())->toArray());
        $this->{$this->getFormPropertyName()}->group = resolve_static(
            $this->{$this->getFormPropertyName()}->getSettingsClass(),
            'group'
        );
    }

    public function render(): View
    {
        return view('flux::livewire.support.settings-component');
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->{$this->getFormPropertyName()}->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Settings')]))
            ->send();

        return true;
    }
}
