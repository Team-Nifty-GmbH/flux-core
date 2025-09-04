<?php

use FluxErp\Models\Language;
use FluxErp\Models\User;

test('model customization', function (): void {
    $class = new class() extends Language
    {
        use FluxErp\Traits\HasParentMorphClass;

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
        use FluxErp\Traits\HasParentMorphClass;

        protected $table = 'languages';
    };
    $this->app->bind(Language::class, get_class($class));

    $language = Language::factory()
        ->create();

    $user = User::factory()
        ->create(['language_id' => $language->id]);

    expect($user->language)->toBeInstanceOf(get_class($class));
});
