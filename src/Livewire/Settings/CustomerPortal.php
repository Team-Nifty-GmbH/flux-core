<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\CreateSettingRequest;
use FluxErp\Http\Requests\UpdateSettingRequest;
use FluxErp\Livewire\Portal\Dashboard;
use FluxErp\Models\Calendar;
use FluxErp\Models\Client;
use FluxErp\Services\SettingService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class CustomerPortal extends Component
{
    use Actions;

    public array $setting = [];

    public array $modules = [];

    public array $calendars = [];

    public function getRules(): array
    {
        return Arr::prependKeysWith(
            ($this->setting['id'] ?? false)
            ? (new UpdateSettingRequest())->rules()
            : (new CreateSettingRequest())->rules(),
            'setting.'
        );
    }

    public function mount(Client $client): void
    {
        $modules = get_subclasses_of(Component::class, 'FluxErp\\Livewire\\Portal');
        $this->modules = array_filter($modules, function ($value) {
            return $value !== Dashboard::class;
        });

        $this->calendars = Calendar::query()->where('is_public', true)->get()->toArray();

        $setting = $client->settings()->where('key', 'customerPortal')->first();

        $this->setting = $setting?->toArray() ??
            [
                'key' => 'customerPortal',
                'model_type' => Client::class,
                'model_id' => $client->id,
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

    public function save(): void
    {
        $validated = $this->validate();

        $isNew = ! ($this->setting['id'] ?? false);
        $response = (new SettingService())->{$isNew ? 'create' : 'update'}($validated['setting']);

        if (! $isNew && $response['status'] !== 200) {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );

            return;
        }

        $this->setting = $isNew ? $response->toArray() : $response['data']->toArray();

        $this->notification()->success(__('Customer Portal settings saved successful.'));

        $this->skipRender();
    }
}
