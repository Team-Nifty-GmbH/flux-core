<?php

namespace FluxErp\Actions\RebateAgreement;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rulesets\RebateAgreement\UpdateRebateAgreementRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateRebateAgreement extends FluxAction
{
    public static function models(): array
    {
        return [RebateAgreement::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateRebateAgreementRuleset::class;
    }

    public function performAction(): Model
    {
        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $rebateAgreement->fill($this->getData());
        $rebateAgreement->save();

        return $rebateAgreement->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'rebate_order_id']);

        if ($rebateAgreement->rebateOrder()->exists()) {
            throw ValidationException::withMessages([
                'id' => ['A settled rebate agreement can no longer be changed.'],
            ])
                ->errorBag('updateRebateAgreement');
        }
    }
}
