<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Comment;
use FluxErp\Models\User;
use FluxErp\Traits\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\CauserResolver;

class CommentTableSeeder extends Seeder
{
    public function run()
    {
        $models = get_subclasses_of(Model::class, 'FluxErp\\Models\\');
        $users = User::all();

        foreach ($models as $model) {
            if (! in_array(Commentable::class, class_uses($model))) {
                continue;
            }

            $records = $model::withoutGlobalScopes()->get(['id']);

            foreach ($records as $record) {
                for ($i = 0; $i < rand(0, 10); $i++) {
                    CauserResolver::setCauser($users->random());

                    Comment::factory()->create([
                        'uuid' => Str::uuid(),
                        'model_type' => morph_alias($model),
                        'model_id' => $record->id,
                    ]);
                }
            }
        }
    }
}
