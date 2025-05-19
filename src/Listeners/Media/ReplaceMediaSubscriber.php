<?php

namespace FluxErp\Listeners\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Contracts\HasMediaForeignKey;

class ReplaceMediaSubscriber
{
    public function handle(FluxAction $action): void
    {
        model_info_all()
            ->filter(
                fn ($modelInfo) => is_a(
                    resolve_static($modelInfo->class, 'class'),
                    HasMediaForeignKey::class,
                    true
                )
            )
            ->each(
                fn ($modelInfo) => resolve_static(
                    $modelInfo->class,
                    'mediaReplaced',
                    [
                        'oldMediaId' => $action->getData('id'),
                        'newMediaId' => $action->getResult()?->getKey(),
                    ]
                )
            );
    }

    public function subscribe(): array
    {
        return [
            'action.executed: ' . resolve_static(ReplaceMedia::class, 'class') => 'handle',
        ];
    }
}
