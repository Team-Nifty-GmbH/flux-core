<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\CoreSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;
use FluxErp\Traits\Model\Commentable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class CoreSettingsForm extends SettingsForm
{
    public bool $formal_salutation = false;

    public bool $install_done = false;

    #[RenderAs(
        type: RenderAs::SELECT,
        options: [
            'multiple' => true,
            ':options' => '\\FluxErp\\Livewire\\Forms\\Settings\\CoreSettingsForm::commentModelOptions()',
        ],
        label: 'Comments with answer to customer',
    )]
    public array $customer_comment_models = [];

    public ?string $license_key = null;

    public static function commentModelOptions(): array
    {
        return collect(Relation::morphMap())
            ->filter(fn (string $class): bool => in_array(
                Commentable::class,
                class_uses_recursive(resolve_static($class, 'class'))
            ))
            ->keys()
            ->map(fn (string $alias): array => ['label' => __(Str::headline($alias)), 'value' => $alias])
            ->sortBy('label')
            ->values()
            ->all();
    }

    public function getSettingsClass(): string
    {
        return CoreSettings::class;
    }
}
