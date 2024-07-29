<?php

namespace FluxErp\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\View\Component;

class AdditionalColumns extends Component
{
    public array $additionalColumns = [];

    public ?int $id;

    public string|Model $model;

    public ?string $wire;

    public bool $table;

    public function __construct(
        string|Model $model,
        ?string $wire = null,
        ?int $id = null,
        bool $table = false,
    ) {
        $this->model = $model;
        $this->id = $id;
        $this->wire = $wire;
        $this->table = $table;
    }

    public function render(): View
    {
        if ($this->model instanceof Model) {
            $additionalColumns = $this->model->additionalColumns?->toArray();
        } elseif ($this->id) {
            $additionalColumns = resolve_static($this->model, 'query')
                ->whereKey($this->id)
                ->first()
                ?->additionalColumns
                ?->toArray();
        } else {
            $additionalColumns = resolve_static($this->model, 'additionalColumnsQuery')->get()?->toArray();
        }
        $this->additionalColumns = $additionalColumns ?: [];

        $this->additionalColumns = Arr::map($this->additionalColumns, function ($value, $key) {
            return $key === 'label' ? __($value) : $value;
        });

        return view('flux::components.additional-columns');
    }
}
