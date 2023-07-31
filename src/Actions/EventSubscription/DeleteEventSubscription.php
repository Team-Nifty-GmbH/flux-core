<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\EventSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeleteEventSubscription extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
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

    public function execute(): ?bool
    {
        return EventSubscription::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
