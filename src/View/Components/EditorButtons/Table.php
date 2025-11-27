<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Traits\EditorDropdownButtonTrait;
use Illuminate\View\Component;

class Table extends Component implements EditorDropdownButton
{
    use EditorDropdownButtonTrait;

    public function icon(): ?string
    {
        return 'table-cells';
    }

    public function tooltip(): ?string
    {
        return 'Table';
    }

    public function dropdownContent(): array
    {
        return [
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Insert Table'),
                'command' => <<<'JS'
                    editor().chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Merge Cells'),
                'command' => <<<'JS'
                    editor().chain().focus().mergeCells().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Split Cell'),
                'command' => <<<'JS'
                    editor().chain().focus().splitCell().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Add Column Before'),
                'command' => <<<'JS'
                    editor().chain().focus().addColumnBefore().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Add Column After'),
                'command' => <<<'JS'
                    editor().chain().focus().addColumnAfter().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Delete Column'),
                'command' => <<<'JS'
                    editor().chain().focus().deleteColumn().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Add Row Before'),
                'command' => <<<'JS'
                    editor().chain().focus().addRowBefore().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Add Row After'),
                'command' => <<<'JS'
                    editor().chain().focus().addRowAfter().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Delete Row'),
                'command' => <<<'JS'
                    editor().chain().focus().deleteRow().run()
                    JS,
            ]),
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full'],
                'text' => __('Delete Table'),
                'command' => <<<'JS'
                    editor().chain().focus().deleteTable().run()
                    JS,
            ]),
        ];
    }
}
