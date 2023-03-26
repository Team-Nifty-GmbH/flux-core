<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Comment;
use FluxErp\Traits\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CommentTableSeeder extends Seeder
{
    public function run()
    {
        $models = get_subclasses_of(Model::class, 'FluxErp\\Models\\');

        foreach ($models as $model) {
            if (! in_array(Commentable::class, class_uses($model))) {
                continue;
            }

            $records = $model::all();

            foreach ($records as $record) {
                for ($i = 0; $i < rand(0, 10); $i++) {
                    Comment::factory()->create([
                        'model_type' => $model,
                        'model_id' => $record->id,
                    ]);
                }
            }
        }
    }
}
