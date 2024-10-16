<?php

namespace FluxErp\Tests\Feature\Customization;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use FluxErp\Traits\HasParentMorphClass;

class ModelTest extends TestCase
{
    public function test_model_customization()
    {
        $class = new class() extends Language
        {
            use HasParentMorphClass;

            protected $table = 'languages';
        };

        $this->app->bind(Language::class, get_class($class));

        $language = Language::factory()
            ->create();

        $this->assertInstanceOf(
            get_class($class),
            resolve_static(Language::class, 'query')
                ->whereKey($language->id)
                ->first()
        );
    }

    public function test_model_relation()
    {
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

        $this->assertInstanceOf(get_class($class), $user->language);
    }
}
