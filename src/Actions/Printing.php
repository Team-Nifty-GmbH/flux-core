<?php

namespace FluxErp\Actions;

use FluxErp\Printing\Printable;
use FluxErp\Rulesets\Printing\PrintingRuleset;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\View\Factory;
use Illuminate\View\View;

class Printing extends FluxAction
{
    public Printable $printable;

    public Model $model;

    public function boot($data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(PrintingRuleset::class, 'getRules');

        $this->validate();
        $this->model = app(Relation::getMorphedModel($this->data['model_type']))->query()
            ->whereKey($this->data['model_id'])
            ->first();
    }

    public static function models(): array
    {
        return [];
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
