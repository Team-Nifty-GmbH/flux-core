<?php

namespace FluxErp\Actions\Target;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Target;
use FluxErp\Rulesets\Target\CreateTargetRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateTarget extends FluxAction
{
    public static function models(): array
    {
        return [Target::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTargetRuleset::class;
    }

    public function performAction(): Target
    {
        $users = Arr::pull($this->data, 'users');

        $target = app(Target::class, ['attributes' => $this->getData()]);
        $target->save();

        $target->users()->attach($users);
        foreach ($target->children()->get('id') as $child) {
            $child->users()->attach($users);
        }

        return $target->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['owner_column'] ??= 'created_by';
    }

    protected function validateData(): void
    {
        parent::validateData();

        $modelClass = morphed_model($this->getData('model_type'));

        $errors = [];
        if (! in_array($this->getData('timeframe_column'), $modelClass::timeframeColumns())) {
            $errors['timeframe_column'] = ['Timeframe column is not valid for the given model type.'];
        }

        if (! in_array($this->getData('aggregate_type'), $modelClass::aggregateTypes())) {
            $errors['aggregate_type'] = ['Aggregate type is not valid for the given model type.'];
        }

        if (! in_array(
            $this->getData('aggregate_column'),
            $modelClass::aggregateColumns($this->getData('aggregate_type'))
        )) {
            $errors['aggregate_column'] = [
                'Aggregate column is not valid for the given model type and aggregate type.',
            ];
        }

        if (! in_array($this->getData('owner_column'), $modelClass::ownerColumns())) {
            $errors['owner_column'] = ['Owner column is not valid for the given model type.'];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createTarget');
        }
    }
}
