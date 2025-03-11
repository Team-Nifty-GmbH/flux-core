<?php

namespace FluxErp\Actions;

use FluxErp\Printing\Printable;
use FluxErp\Rulesets\Printing\PrintingRuleset;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Factory;
use Illuminate\View\View;

class Printing extends FluxAction
{
    public Model $model;

    public Printable $printable;

    public static function models(): array
    {
        return [];
    }

    protected function getRulesets(): string|array
    {
        return PrintingRuleset::class;
    }

    public function boot($data): void
    {
        parent::boot($data);

        $this->validate();
        $this->model = morphed_model($this->data['model_type'])::query()
            ->whereKey($this->data['model_id'])
            ->first();
    }

    public function performAction(): View|Factory|PrintableView
    {
        $this->printable = $this->model
            ->print()
            ->preview(data_get($this->data, 'preview', false) && ! data_get($this->data, 'html', false));
        $printClass = $this->printable->getViewClass($this->data['view']);

        return ($this->data['html'] ?? false)
            ? $this->printable->renderView($printClass)
            : $this->printable->printView($printClass);
    }
}
