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
        $userShares = Arr::pull($this->data, 'user_shares');
        $targetValue = data_get($this->data, 'target_value');

        $target = app(Target::class, ['attributes' => $this->getData()]);
        $target->save();

        if ($users) {
            $pivotData = [];
            foreach ($users as $id) {
                $abs = data_get($userShares, $id . '.absolute');
                $rel = data_get($userShares, $id . '.relative');
                $pivotData[$id] = [
                    'target_share' => is_null($rel)
                    ? bcdiv($abs, $targetValue)
                    : bcdiv($rel, 100),
                    'target_share_is_percentage' => ! is_null($rel),
                ];
            }

            $target->users()->attach($pivotData);

            foreach ($target->children()->get() as $child) {
                $child->users()->attach($pivotData);
            }
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

        $isGroup = $this->getData('is_group_target');
        $users = $this->getData('users', []);
        $shares = $this->getData('user_shares', []);

        if ($isGroup && ! empty($users)) {
            $hasRel = false;
            $hasAbs = false;

            foreach ($users as $uid) {
                $rel = data_get($shares, "$uid.relative");
                $abs = data_get($shares, "$uid.absolute");

                if (! is_null($rel)) {
                    $hasRel = true;
                }
                if (! is_null($abs)) {
                    $hasAbs = true;
                }
            }

            if ($hasRel && $hasAbs) {
                $errors['user_shares'] = array_merge($errors['user_shares'] ?? [], [
                    __('Relative and absolute shares are mutually exclusive'),
                ]);
            }

            foreach ($users as $uid) {
                $rel = data_get($shares, "$uid.relative");
                $abs = data_get($shares, "$uid.absolute");
                if (is_null($rel) && is_null($abs)) {
                    $errors["user_shares.$uid"] = ['Provide relative or absolute share for each selected user'];
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createTarget');
        }
    }
}
