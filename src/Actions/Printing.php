<?php

namespace FluxErp\Actions;

use FluxErp\Printing\Printable;
use FluxErp\Rulesets\Printing\PrintingRuleset;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Printing extends FluxAction
{
    public Model $model;

    public Printable $printable;

    public static function models(): array
    {
        return [];
    }

    protected static function getReturnResult(): bool
    {
        return parent::getReturnResult() ?? true;
    }

    protected function getRulesets(): string|array
    {
        return PrintingRuleset::class;
    }

    public function performAction(): HtmlString|PrintableView
    {
        $this->model = morphed_model($this->data['model_type'])::query()
            ->whereKey($this->data['model_id'])
            ->first();

        $this->printable = $this->model
            ->print()
            ->preview($this->getData('preview') && ! $this->getData('html'));
        $printClass = $this->printable->getViewClass($this->getData('view'));
        $params = $this->getData('params') ?? [];

        return $this->getData('html')
            ? $this->printable->renderView($printClass, ...$params)
            : $this->printable->printView($printClass, ...$params);
    }

    protected function validateData(): void
    {
        parent::validateData();

        $model = morphed_model($this->getData('model_type'))::query()
            ->whereKey($this->getData('model_id'))
            ->first();

        if (array_key_exists($this->getData('view'), $model->resolvePrintViews())) {
            return;
        }

        $permission = print_view_to_permission($this->getData('view'), $model->getMorphClass());

        try {
            $denied = auth()->user()?->hasPermissionTo($permission) === false;
        } catch (PermissionDoesNotExist) {
            $denied = false;
        }

        if ($denied) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        throw ValidationException::withMessages([
            'view' => [__('validation.in', ['attribute' => 'view'])],
        ]);
    }
}
