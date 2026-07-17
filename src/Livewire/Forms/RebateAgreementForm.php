<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\RebateAgreement\CreateRebateAgreement;
use FluxErp\Actions\RebateAgreement\DeleteRebateAgreement;
use FluxErp\Actions\RebateAgreement\UpdateRebateAgreement;
use FluxErp\Models\RebateAgreement;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class RebateAgreementForm extends FluxForm
{
    use SupportsAutoRender;

    public ?int $contact_id = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    public ?string $period_end = null;

    public ?string $period_start = null;

    #[Locked]
    public ?string $settled_at = null;

    public array $tiers = [];

    protected static function getModel(): string
    {
        return RebateAgreement::class;
    }

    public function fill($values): void
    {
        parent::fill($values);

        $this->tiers = array_map(
            fn (array $tier) => [
                'from_volume' => data_get($tier, 'from_volume'),
                'percentage' => bcround(bcmul(data_get($tier, 'percentage') ?? 0, 100), 2),
            ],
            $this->tiers ?: []
        );
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['tiers'] = array_map(
            fn (array $tier) => [
                'from_volume' => data_get($tier, 'from_volume'),
                'percentage' => bcdiv(data_get($tier, 'percentage') ?? 0, 100, 10),
            ],
            $data['tiers'] ?? []
        );

        return $data;
    }

    public function addTier(): void
    {
        $this->tiers[] = ['from_volume' => null, 'percentage' => null];
    }

    public function removeTier(int $index): void
    {
        unset($this->tiers[$index]);

        $this->tiers = array_values($this->tiers);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateRebateAgreement::class,
            'update' => UpdateRebateAgreement::class,
            'delete' => DeleteRebateAgreement::class,
        ];
    }
}
