<?php

use FluxErp\Contracts\MentionsContent;
use FluxErp\Models\Comment;
use FluxErp\Traits\Model\RecordsMentions;

it('every model using RecordsMentions implements MentionsContent', function (): void {
    $models = collect(get_models_with_trait(RecordsMentions::class, fn (string $class): string => $class));

    // Discovery must actually find the canonical source model, otherwise the
    // assertion below would pass vacuously.
    expect($models)->toContain(Comment::class);

    $violators = $models
        ->reject(fn (string $class): bool => in_array(MentionsContent::class, class_implements($class), true))
        ->values()
        ->all();

    expect($violators)->toBe([]);
});
