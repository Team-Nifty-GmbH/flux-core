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
        $userShares = Arr::pull($this->data, 'user_shares', []);
        $targetValue = data_get($this->data, 'target_value');

        $target = resolve_static(Target::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $target->fill($this->getData());
        $target->save();

        if ($users && $userShares && $target->is_group_target) {
            $pivotData = [];
            foreach ($users as $id) {
                $abs = data_get($userShares, $id . '.absolute');
                $rel = data_get($userShares, $id . '.relative');
                $pivotData[$id] = [
                    'target_allocation' => is_null($rel)
                        ? bcdiv($abs, $targetValue)
                        : bcdiv($rel, 100),
                ];
            }
            $target->users()->sync($pivotData);

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

        if ($this->getData('is_group_target')) {
            $isGroup = $this->getData('is_group_target');
            $users = $this->getData('users', []);
            $shares = $this->getData('user_shares', []);
            $targetValue = $this->getData('target_value', 0);

            if ($isGroup && ! empty($users)) {
                $sumRel = 0.0;
                $sumAbs = 0.0;
                $hasRel = false;
                $hasAbs = false;

                foreach ($users as $uid) {
                    $rel = data_get($shares, "$uid.relative");
                    $abs = data_get($shares, "$uid.absolute");

                    if (! is_null($rel)) {
                        $hasRel = true;
                        $sumRel += $rel;
                    }
                    if (! is_null($abs)) {
                        $hasAbs = true;
                        $sumAbs += $abs;
                    }
                }

                if ($hasRel) {
                    if (abs($sumRel - 100) > 0) {
                        $errors['user_shares'] = [__('Relative shares must sum to 100 %')];
                    }
                }

                if ($hasRel && $hasAbs) {
                    $errors['user_shares'] = array_merge($errors['user_shares'] ?? [], [
                        __('Relative and absolute shares are mutually exclusive'),
                    ]);
                }

                if ($hasAbs) {
                    if (abs($sumAbs - $targetValue) > 0) {
                        $errors['user_shares'] = array_merge($errors['user_shares'] ?? [], [
                            __('Absolute shares must sum to the target value'),
                        ]);
                    }
                }

                foreach ($users as $uid) {
                    $rel = data_get($shares, "$uid.relative");
                    $abs = data_get($shares, "$uid.absolute");
                    if (is_null($rel) && is_null($abs)) {
                        $errors["user_shares.$uid"] = ['Provide relative or absolute share for each selected user'];
                    }
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createTarget');
        }
    }
}
