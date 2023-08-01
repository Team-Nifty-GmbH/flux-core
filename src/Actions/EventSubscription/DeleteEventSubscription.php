<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EventSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeleteEventSubscription extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                Rule::exists('event_subscriptions', 'id')->where('user_id', Auth::id()),
            ],
        ];
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function performAction(): ?bool
    {
        return EventSubscription::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
