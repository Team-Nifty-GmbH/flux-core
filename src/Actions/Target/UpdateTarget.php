<?php

namespace FluxErp\Actions\Target;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Target;
use FluxErp\Rulesets\Target\UpdateTargetRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateTarget extends FluxAction
{
    public static function models(): array
    {
        return [Target::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateTargetRuleset::class;
    }

    public function performAction(): Model
    {
        $users = Arr::pull($this->data, 'users');

        $target = resolve_static(Target::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $target->fill($this->getData());
        $target->save();

        if (! is_null($users)) {
            $target->users()->sync($users);

            foreach ($target->children()->get('id') as $child) {
                $child->users()->sync($users);
            }
        }

        return $target->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $modelClass = morphed_model(
            resolve_static(Target::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('model_type')
        );

        $errors = [];
        if ($this->getData('timeframe_column')
            && ! in_array($this->getData('timeframe_column'), $modelClass::timeframeColumns())
        ) {
            $errors['timeframe_column'] = ['Timeframe column is not valid for the given model type.'];
        }

        if (
            $this->getData('aggregate_type')
            && ! in_array($this->getData('aggregate_type'), $modelClass::aggregateTypes())
        ) {
            $errors['aggregate_type'] = ['Aggregate type is not valid for the given model type.'];
        }

        if (
            $this->getData('aggregate_column')
            && ! in_array(
                $this->getData('aggregate_column'),
                $modelClass::aggregateColumns($this->getData('aggregate_type'))
            )
        ) {
            $errors['aggregate_column'] = [
                'Aggregate column is not valid for the given model type and aggregate type.',
            ];
        }

        if (
            $this->getData('owner_column')
            && ! in_array($this->getData('owner_column'), $modelClass::ownerColumns())
        ) {
            $errors['owner_column'] = ['Owner column is not valid for the given model type.'];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createTarget');
        }
    }
}
