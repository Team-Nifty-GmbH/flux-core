<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Setting\UpdateSetting;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

abstract class SettingsForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public string $group;

    abstract public function getSettingsClass(): string;

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->group = resolve_static($this->getSettingsClass(), 'group');
    }

    public function toActionData(): array
    {
        return array_merge(
            [
                'settings_class' => resolve_static($this->getSettingsClass(), 'class'),
                'group' => resolve_static($this->getSettingsClass(), 'group'),
            ],
            parent::toActionData()
        );
    }

    protected function getActions(): array
    {
        return [
            'update' => UpdateSetting::class,
        ];
    }

    protected function getKey(): string
    {
        return 'group';
    }
}
