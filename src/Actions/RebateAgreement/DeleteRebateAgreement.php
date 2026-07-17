<?php

namespace FluxErp\Actions\RebateAgreement;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rulesets\RebateAgreement\DeleteRebateAgreementRuleset;
use Illuminate\Validation\ValidationException;

class DeleteRebateAgreement extends FluxAction
{
    public static function models(): array
    {
        return [RebateAgreement::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteRebateAgreementRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if ($rebateAgreement->settled_at) {
            throw ValidationException::withMessages([
                'id' => [__('A settled rebate agreement can no longer be deleted.')],
            ])->errorBag('deleteRebateAgreement');
        }
    }
}
