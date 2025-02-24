<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Actions\Discount\DeleteDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Livewire\DataTables\DiscountList;
use FluxErp\Livewire\Forms\Contact\DiscountForm;
use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Discounts extends DiscountList
{
    protected ?string $includeBefore = 'flux::livewire.contact.accounting.discounts';

    public DiscountForm $discountForm;

    #[Modelable]
    public int $contactId;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->xOnClick(<<<'JS'
                    $modalOpen('edit-discount');
                JS)
                ->color('indigo'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->when(fn () => resolve_static(UpdateDiscount::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)')
                ->color('indigo'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->when(fn () => resolve_static(DeleteDiscount::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Discount')]),
                    'wire:click' => 'delete(record.id)',
                ])
                ->color('red'),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return parent::getBuilder($builder)
            ->whereRelation(
                'contacts',
                'contacts.id',
                $this->contactId
            );
    }

    #[Renderless]
    public function resetDiscount(): void
    {
        $this->discountForm->reset();
    }

    #[Renderless]
    public function edit(Discount $discount): void
    {
        $this->discountForm->reset();
        $this->discountForm->fill($discount);

        $this->js(<<<'JS'
            $modalOpen('edit-discount');
        JS);
    }

    #[Renderless]
    public function delete(Discount $discount): void
    {
        $this->discountForm->reset();
        $this->discountForm->fill($discount);

        try {
            $this->discountForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    #[Renderless]
    public function save(): bool
    {
        $isNew = is_null($this->discountForm->id);

        try {
            $this->discountForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        if ($isNew) {
            try {
                UpdateContact::make([
                    'id' => $this->contactId,
                    'discounts_pivot_sync_type' => 'attach',
                    'discounts' => [
                        [
                            'id' => $this->discountForm->id,
                        ],
                    ],
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        $this->loadData();

        return true;
    }
}
