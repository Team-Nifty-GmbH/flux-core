<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Language;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Traits\Model\HasParentMorphClass;

test('model customization', function (): void {
    $class = new class() extends Language
    {
        use HasParentMorphClass;

        protected $table = 'languages';
    };
    $this->app->bind(Language::class, get_class($class));

    $language = Language::factory()
        ->create();

    expect(resolve_static(Language::class, 'query')
        ->whereKey($language->id)
        ->first())->toBeInstanceOf(get_class($class));
});

test('model relation', function (): void {
    $class = new class() extends Language
    {
        use HasParentMorphClass;

        protected $table = 'languages';
    };
    $this->app->bind(Language::class, get_class($class));

    $language = Language::factory()
        ->create();

    $user = User::factory()
        ->create(['language_id' => $language->id]);

    expect($user->language)->toBeInstanceOf(get_class($class));
});

test('model morph to eager relation', function (): void {
    $class = new class() extends Ticket
    {
        use HasParentMorphClass;

        protected $table = 'tickets';
    };
    $this->app->bind(Ticket::class, get_class($class));

    $user = User::factory()->create();

    $ticket = Ticket::factory()
        ->create([
            'authenticatable_type' => $user->getMorphClass(),
            'authenticatable_id' => $user->getKey(),
        ]);

    $comment = Comment::factory()
        ->create([
            'model_type' => $ticket->getMorphClass(),
            'model_id' => $ticket->getKey(),
        ]);

    expect($comment->model)->toBeInstanceOf(get_class($class));
});
