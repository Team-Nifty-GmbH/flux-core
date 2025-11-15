<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Portal\Dashboard;
use FluxErp\Models\Calendar;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Setting\CreateSettingRuleset;
use FluxErp\Rulesets\Setting\UpdateSettingRuleset;
use FluxErp\Services\SettingService;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

class CustomerPortal extends Component
{
    use Actions;

    public array $calendars = [];

    public array $modules = [];

    public array $setting = [];

    public function mount(Tenant $tenant): void
    {
        $modules = get_subclasses_of(Component::class, 'FluxErp\\Livewire\\Portal');
        $this->modules = array_filter($modules, function ($value) {
            return $value !== Dashboard::class;
        });

        $this->calendars = resolve_static(Calendar::class, 'query')
            ->where('is_public', true)
            ->get()
            ->toArray();

        $setting = $tenant->settings()
            ->where('key', 'customerPortal')
            ->first();

        $this->setting = $setting?->toArray() ??
            [
                'key' => 'customerPortal',
                'model_type' => morph_alias(Tenant::class),
                'model_id' => $tenant->id,
                'settings' => [
                    'nav' => [
                        'background' => [
                            'start' => '#000000',
                            'end' => '#000000',
                            'angle' => 0,
                        ],
                        'append_links' => [
                        ],
                    ],
                    'custom_css' => '',
                    'dashboard_module' => null,
                    'calendars' => [],
                ],
            ];
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.customer-portal');
    }

    public function getRules(): array
    {
        return Arr::prependKeysWith(
            ($this->setting['id'] ?? false)
            ? resolve_static(UpdateSettingRuleset::class, 'getRules')
            : resolve_static(CreateSettingRuleset::class, 'getRules'),
            'setting.'
        );
    }

    public function save(): void
    {
        $validated = $this->validate();

        $isNew = ! ($this->setting['id'] ?? false);
        $response = app(SettingService::class)->{$isNew ? 'create' : 'update'}($validated['setting']);

        if (! $isNew && $response['status'] !== 200) {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            )->send();

            return;
        }

        $this->setting = $isNew ? $response->toArray() : $response['data']->toArray();

        $this->notification()->success(__(':model saved', ['model' => __('Customer Portal Settings')]))->send();

        $this->skipRender();
    }
}
