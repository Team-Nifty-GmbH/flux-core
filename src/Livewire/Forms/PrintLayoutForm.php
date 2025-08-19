<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PrintLayout\CreatePrintLayout;
use FluxErp\Actions\PrintLayout\DeletePrintLayout;
use FluxErp\Actions\PrintLayout\UpdatePrintLayout;
use Livewire\Attributes\Locked;

class PrintLayoutForm extends FluxForm
{
    #[Locked]
    public ?int $client_id = null;

    #[Locked]
    public ?string $name = null;

    public array $margin = [];

    public array $header = [];

    public array $footer = [];

    public array $first_page_header = [];

    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?string $model_type = null;

    public array $temporaryMedia = [];


    public function fill($values): void
    {
        if (is_null(data_get($values, 'margin'))) {
            data_set($values, 'margin', []);
        }

        if (is_null(data_get($values, 'header'))) {
            data_set($values, 'header', []);
        }

        if (is_null(data_get($values, 'footer'))) {
            data_set($values, 'footer', []);
        }

        if (is_null(data_get($values, 'first_page_header'))) {
            data_set($values, 'first_page_header', []);
        }

        parent::fill($values);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreatePrintLayout::class,
            'update' => UpdatePrintLayout::class,
            'delete' => DeletePrintLayout::class,
        ];
    }
}
