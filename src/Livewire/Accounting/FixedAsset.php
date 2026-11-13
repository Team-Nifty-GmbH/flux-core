<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\DeleteFixedAsset;
use FluxErp\Actions\FixedAsset\ReverseFixedAssetDepreciations;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\FixedAssetForm;
use FluxErp\Models\FixedAsset as FixedAssetModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class FixedAsset extends Component
{
    use Actions, WithTabs;

    public FixedAssetForm $fixedAsset;

    public array $queryString = [
        'tab' => ['except' => 'fixed-asset.general'],
    ];

    public ?string $reverseFrom = null;

    public string $tab = 'fixed-asset.general';

    public function mount(string $id): void
    {
        try {
            $this->getTabButton($this->tab);
        } catch (Throwable) {
            throw new NotFoundHttpException('Tab not found');
        }

        $this->fixedAsset->fill($this->loadFixedAsset($id));
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.accounting.fixed-asset');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('fixed-asset.general')
                ->text(__('General')),
            TabButton::make('fixed-asset.depreciations')
                ->text(__('Depreciation Schedule')),
            TabButton::make('fixed-asset.bookings')
                ->text(__('Bookings')),
        ];
    }

    public function book(): bool
    {
        try {
            $booked = BookFixedAssetDepreciations::make(['fixed_asset_id' => $this->fixedAsset->id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->resetForm();
        $this->reloadTables();

        $this->toast()
            ->success(__(':count depreciations booked', ['count' => $booked]))
            ->send();

        return true;
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            DeleteFixedAsset::make(['id' => $this->fixedAsset->id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->fixedAsset);

            return;
        }

        $this->redirect(route('accounting.fixed-assets'), navigate: true);
    }

    public function resetForm(): void
    {
        $fixedAsset = $this->loadFixedAsset($this->fixedAsset->id);

        $this->fixedAsset->reset();
        $this->fixedAsset->fill($fixedAsset);
    }

    public function reverse(): bool
    {
        try {
            $reversed = ReverseFixedAssetDepreciations::make([
                'fixed_asset_id' => $this->fixedAsset->id,
                'from' => $this->reverseFrom,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->reverseFrom = null;
        $this->resetForm();
        $this->reloadTables();

        $this->toast()
            ->success(__(':count depreciations reversed', ['count' => $reversed]))
            ->send();

        return true;
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->fixedAsset->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->fixedAsset);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Fixed Asset')]))
            ->send();

        return true;
    }

    protected function reloadTables(): void
    {
        $this->dispatch('loadData')->to('accounting.fixed-asset-depreciations');
        $this->dispatch('loadData')->to('accounting.fixed-asset-ledger-bookings');
    }

    protected function loadFixedAsset(int|string $id): FixedAssetModel
    {
        return resolve_static(FixedAssetModel::class, 'query')
            ->whereKey($id)
            ->firstOrFail();
    }
}
