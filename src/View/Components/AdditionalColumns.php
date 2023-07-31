<?php

namespace FluxErp\View\Components;

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

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        string|Model $model,
        string $wire = null,
        int $id = null,
        bool $table = false,
    ) {
        $this->model = $model;
        $this->id = $id;
        $this->wire = $wire;
        $this->table = $table;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if ($this->model instanceof Model) {
            $additionalColumns = $this->model->additionalColumns?->toArray();
        } elseif ($this->id) {
            $additionalColumns = $this->model::query()->whereKey($this->id)->first()?->additionalColumns?->toArray();
        } else {
            $additionalColumns = $this->model::additionalColumnsQuery()->get()?->toArray();
        }
        $this->additionalColumns = $additionalColumns ?: [];

        $this->additionalColumns = Arr::map($this->additionalColumns, function ($value, $key) {
            return $key === 'label' ? __($value) : $value;
        });

        return view('flux::components.additional-columns');
    }
}
