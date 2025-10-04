<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'comment' => fake()->realText(),
        ];
    }
}
