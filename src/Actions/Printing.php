<?php

namespace FluxErp\Actions;

use FluxErp\Http\Requests\PrintingRequest;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Factory;
use Illuminate\View\View;

class Printing extends FluxAction
{
    public \FluxErp\Printing\Printable $printable;

    public Model $model;

    public function boot($data): void
    {
        parent::boot($data);
        $this->rules = (new PrintingRequest())->rules();

        $this->model = $this->data['model_type']::query()->whereKey($this->data['model_id'])->firstOrFail();
    }

    public static function models(): array
    {
        return [];
    }

    public function performAction(): View|Factory|PrintableView
    {
        $this->printable = $this->model->print()->preview(data_get($this->data, 'preview', false) && ! data_get($this->data, 'html', true));
        $printClass = $this->printable->getViewClass($this->data['view']);

        return ($this->data['html'] ?? false)
            ? $this->printable->renderView($printClass)
            : $this->printable->printView($printClass);
    }
}
