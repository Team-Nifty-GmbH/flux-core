<?php

use FluxErp\Models\Category;
use FluxErp\Models\Comment;
use FluxErp\Models\Language;
use FluxErp\Models\Product;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Traits\Model\HasParentMorphClass;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('media can be added to a category through a bound subclass', function (): void {
    // The media library builds the conversion owner with `new $modelName` off the
    // morph map, so the base model has to be media capable. A subclass adding the
    // trait is never seen there.
    $class = new class() extends Category
    {
        use HasParentMorphClass;

        protected $table = 'categories';

        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('banner')->singleFile();
        }
    };
    $this->app->bind(Category::class, get_class($class));

    Storage::fake('public');

    $category = resolve_static(Category::class, 'query')->create([
        'name' => 'Tea',
        'model_type' => morph_alias(Product::class),
    ]);

    $category->addMedia(UploadedFile::fake()->image('banner.jpg'))
        ->toMediaCollection('banner');

    expect($category->getMedia('banner')->first()->file_name)->toBe('banner.jpg');
});
