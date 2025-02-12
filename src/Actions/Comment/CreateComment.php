<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\Comment\CreateCommentRuleset;
use FluxErp\Traits\Notifiable;
use Illuminate\Database\Eloquent\Model;

class CreateComment extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateCommentRuleset::class;
    }

    public static function models(): array
    {
        return [Comment::class, EventSubscription::class];
    }

    public function performAction(): Comment
    {
        preg_match_all('/data-id="([^:]+:\d+)"/', $this->data['comment'], $matches);
        $mentions = collect($matches[1])->map(function ($mention) {
            return morph_to($mention);
        })
            ->add(auth()->user())
            ->filter() // filter null values if morph was not possible
            ->filter(fn (Model $notifiable) => in_array(Notifiable::class, class_uses_recursive($notifiable)));

        foreach ($mentions as $mention) {
            if ($mention->eventSubscriptions()
                ->where([
                    'event' => eloquent_model_event('created', Comment::class),
                    'model_type' => $this->data['model_type'],
                    'model_id' => $this->data['model_id'],
                ])
                ->doesntExist()
            ) {
                CreateEventSubscription::make([
                    'event' => eloquent_model_event(
                        'created',
                        resolve_static(Comment::class, 'class')
                    ),
                    'subscribable_id' => $mention->getKey(),
                    'subscribable_type' => $mention->getMorphClass(),
                    'model_type' => $this->data['model_type'],
                    'model_id' => $this->data['model_id'],
                    'is_broadcast' => false,
                    'is_notifiable' => true,
                ])
                    ->validate()
                    ->execute();
            }
        }

        $comment = app(Comment::class, ['attributes' => $this->data]);
        $comment->save();

        return $comment->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_sticky'] ??= false;
        $this->data['is_internal'] ??= true;
    }
}
