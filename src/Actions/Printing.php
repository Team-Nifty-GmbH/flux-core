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
    public static bool $returnResult = true;

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

    public function performAction(): View|Factory|PrintableView
    {
        $this->model = morphed_model($this->data['model_type'])::query()
            ->whereKey($this->data['model_id'])
            ->first();

        $this->printable = $this->model
            ->print()
            ->preview($this->getData('preview') && ! $this->getData('html'));
        $printClass = $this->printable->getViewClass($this->getData('view'));

        return $this->getData('html')
            ? $this->printable->renderView($printClass)
            : $this->printable->printView($printClass);
    }
}
