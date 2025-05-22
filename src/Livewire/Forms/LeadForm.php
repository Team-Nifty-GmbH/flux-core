<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Lead\CreateLead;
use FluxErp\Actions\Lead\DeleteLead;
use FluxErp\Actions\Lead\UpdateLead;
use FluxErp\Models\Lead;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class LeadForm extends FluxForm
{
    use SupportsAutoRender;

    public ?int $address_id = null;

    #[Locked]
    public ?string $addressLabel;

    #[Locked]
    public ?string $addressUrl;

    #[Locked]
    public ?string $avatar;

    public ?array $categories = null;

    public ?string $description = null;

    public ?string $end = null;

    public string|float|null $expected_gross_profit = null;

    public string|float|null $expected_revenue = null;

    #[Locked]
    public ?int $id = null;

    public ?int $lead_state_id = null;

    public ?string $loss_reason = null;

    public ?string $name = null;

    public string|float|null $probability_percentage = null;

    public ?int $recommended_by_address_id = null;

    public int $score = 0;

    public ?string $start = null;

    public ?array $tags = null;

    public ?int $user_id = null;

    public function fill($values): void
    {
        if ($values instanceof Lead) {
            $values->loadMissing(['tags:id', 'categories:id']);
            $this->avatar = $values->address?->getAvatarUrl();
            $this->addressUrl = $values->address?->getUrl();
            $this->addressLabel = $values->address?->getLabel();

            $values = $values->toArray();
            $values['tags'] = array_column($values['tags'] ?? [], 'id');
            $values['categories'] = array_column($values['categories'] ?? [], 'id');
        }

        parent::fill($values);

        $this->probability_percentage = ! is_null($this->probability_percentage)
            ? bcmul($this->probability_percentage, 100)
            : null;
        $this->user_id ??= auth()->id();
    }

    public function reset(...$properties): void
    {
        parent::reset($properties);

        $this->user_id ??= auth()->id();
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['probability_percentage'] = ! is_null($this->probability_percentage)
            ? bcdiv($this->probability_percentage, 100)
            : null;

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateLead::class,
            'update' => UpdateLead::class,
            'delete' => DeleteLead::class,
        ];
    }
}
