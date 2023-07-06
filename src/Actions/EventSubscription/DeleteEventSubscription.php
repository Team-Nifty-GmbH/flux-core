<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\EventSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeleteEventSubscription implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => [
                'required',
                'integer',
                Rule::exists('event_subscriptions', 'id')->where('user_id', Auth::id()),
            ],
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'event-subscription.delete';
    }

    public static function description(): string|null
    {
        return 'delete event subscription';
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function execute()
    {
        return EventSubscription::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
