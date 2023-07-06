<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdateEventSubscriptionRequest;
use FluxErp\Models\EventSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateEventSubscription implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateEventSubscriptionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'event-subscription.update';
    }

    public static function description(): string|null
    {
        return 'update event subscription';
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function execute(): Model
    {
        $eventSubscription = EventSubscription::query()
            ->whereKey($this->data['id'])
            ->first();

        $eventSubscription->fill($this->data);
        $eventSubscription->save();

        return $eventSubscription->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $eventClass = Helper::classExists(classString: ucfirst($this->data['event']), isEvent: true);

        if ($this->data['event'] !== '*' && ! $eventClass) {
            $eventExploded = explode(':', str_replace(' ', '', $this->data['event']));
            $model = $eventExploded[1] ?? null;
            $eloquentEvent = $model ? eloquent_model_event($eventExploded[0], $model) : null;
        } else {
            $eloquentEvent = $this->data['event'];
        }

        if (! $eventClass && ! $eloquentEvent) {
            throw ValidationException::withMessages([
                'event' => [__('Event not found')]
            ]);
        }

        $this->data['event'] = $eventClass ?: $eloquentEvent;
        $this->data['user_id'] ??= Auth::id();

        if (EventSubscription::query()
            ->whereKeyNot($this->data['id'])
            ->where('event', $this->data['event'])
            ->where('user_id', $this->data['user_id'])
            ->where('model_type', $this->data['model_type'])
            ->where(function (Builder $query) {
                return $query->where('model_id', $this->data['model_id'])
                    ->orWhereNull('model_id');
            })
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'subscription' => [__('Already subscribed')]
            ])->errorBag('createEventSubscription');
        }

        return $this;
    }
}
