<?php

namespace FluxErp\Livewire\Settings;

use ErrorException;
use FluxErp\Livewire\DataTables\TokenList;
use FluxErp\Livewire\Forms\TokenForm;
use FluxErp\Models\Permission;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

class Tokens extends TokenList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::save as parentSave;
        DataTableHasFormEdit::edit as parentEdit;
    }

    public array $permissions = [];

    #[DataTableForm]
    public TokenForm $tokenForm;

    protected ?string $includeBefore = 'flux::livewire.settings.tokens';

    public function edit(string|int|null $id = null): void
    {
        $this->parentEdit($id);

        $this->permissions = $this->getPermissionTree();
    }

    #[Renderless]
    public function getPermissionTree(): array
    {
        $permissions = Permission::query()
            ->where('guard_name', 'token')
            ->pluck('id', 'name')
            ->toArray();

        return $this->preparePermissions(Arr::undotToTree(
            array: $permissions,
            translate: fn (string $key) => $key === 'get' ? __('permission.get') : __(Str::headline($key))
        ));
    }

    public function save(): bool
    {
        if ($result = $this->parentSave()) {
            $this->js(<<<'JS'
                $modalOpen('copy-token-modal')
            JS);
        }

        return $result;
    }

    protected function preparePermissions(array $tree, array $parent = []): array
    {
        foreach ($tree as $key => &$value) {
            $label = data_get($value, 'label');

            if ($parent) {
                try {
                    data_set($tree, $key . '.path', data_get($parent, 'path') . ' -> ' . $label);
                } catch (ErrorException) {
                    continue;
                }
            } else {
                data_set($tree, $key . '.path', $label);
            }

            if ($children = data_get($value, 'children')) {
                $value['children'] = $this->preparePermissions($children, $value);
            }
        }

        return $tree;
    }
}
