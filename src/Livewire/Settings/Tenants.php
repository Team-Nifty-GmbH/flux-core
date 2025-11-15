<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Tenant\CreateTenant;
use FluxErp\Actions\Tenant\UpdateTenant;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\TenantList;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Livewire\Forms\TenantForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Country;
use FluxErp\Models\Scopes\UserTenantScope;
use FluxErp\Models\Tenant;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Tenants extends TenantList
{
    use Actions, WithFileUploads, WithTabs;

    public TenantForm $tenant;

    public MediaUploadForm $logo;

    public MediaUploadForm $logoSmall;

    public string $tab = 'general';

    protected string $view = 'flux::livewire.settings.tenants';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'wire:click' => 'show()',
                ])
                ->when(resolve_static(CreateTenant::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->attributes([
                    'wire:click' => 'show(record.id)',
                ])
                ->when(resolve_static(UpdateTenant::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function delete(): bool
    {
        try {
            $this->tenant->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('general')
                ->text(__('General')),
            TabButton::make('settings.tenant.logos')
                ->text(__('Logos')),
            TabButton::make('settings.tenant.terms-and-conditions')
                ->text(__('Terms and Conditions')),
            TabButton::make('settings.tenant.sepa')
                ->text(__('SEPA')),
        ];
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->tenant->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->logo->model_type = morph_alias(Tenant::class);
        $this->logo->model_id = $this->tenant->id;
        $this->logo->collection_name = 'logo';

        $this->logoSmall->model_type = app(Tenant::class)->getMorphClass();
        $this->logoSmall->model_id = $this->tenant->id;
        $this->logoSmall->collection_name = 'logo_small';

        if ($this->logo->stagedFiles || $this->logo->id) {
            try {
                $this->logo->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        if ($this->logoSmall->stagedFiles || $this->logoSmall->id) {
            try {
                $this->logoSmall->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->notification()->success(__(':model saved', ['model' => __('Tenant')]))->send();

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function show(?Tenant $record = null): void
    {
        $this->tenant->reset();
        $record->load('bankConnections:id');
        $tenant = $record->toArray();
        $tenant['bank_connections'] = array_column($tenant['bank_connections'], 'id');
        $tenant['opening_hours'] = $tenant['opening_hours'] ?? [];

        $this->tenant->fill($tenant);

        $this->logo->fill($record->getMedia('logo')->first() ?? []);
        $this->logoSmall->fill($record->getMedia('logo_small')->first() ?? []);

        $this->js(<<<'JS'
            $modalOpen('edit-tenant');
        JS);
    }

    public function showCustomerPortal(Tenant $record): void
    {
        $this->redirect(route('settings.customer-portal', ['tenant' => $record->id]), true);
    }

    public function updatingTab(): void
    {
        $this->forceRender();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope(UserTenantScope::class);
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'bankConnections' => resolve_static(BankConnection::class, 'query')
                    ->where('is_active', true)
                    ->select(['bank_connections.id', 'name'])
                    ->get()
                    ->toArray(),
                'countries' => resolve_static(Country::class, 'query')
                    ->orderBy('iso_alpha2', 'ASC')
                    ->select(['id', 'name', 'iso_alpha2'])
                    ->get()
                    ->toArray(),
            ]
        );
    }
}
