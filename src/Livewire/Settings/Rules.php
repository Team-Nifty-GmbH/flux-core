<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Rule\CreateRule;
use FluxErp\Actions\Rule\DeleteRule;
use FluxErp\Actions\Rule\UpdateRule;
use FluxErp\Livewire\DataTables\RuleList;
use FluxErp\Livewire\Forms\RuleForm;
use FluxErp\Models\Rule;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Rules extends RuleList
{
    use Actions;

    public RuleForm $ruleForm;

    protected ?string $includeBefore = 'flux::livewire.settings.rules';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateRule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateRule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteRule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Rule')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    #[Renderless]
    public function edit(?Rule $rule = null): void
    {
        $this->ruleForm->reset();

        if ($rule?->getKey()) {
            $this->ruleForm->fill($rule);
        }

        $this->js(<<<'JS'
            $tsui.open.modal('edit-rule-modal');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->ruleForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function delete(Rule $rule): bool
    {
        try {
            $this->ruleForm->fill($rule);
            $this->ruleForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
