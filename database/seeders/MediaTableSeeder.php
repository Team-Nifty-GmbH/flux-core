<?php

namespace FluxErp\Database\Seeders;

use Faker\Factory;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class MediaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = get_subclasses_of(Model::class, 'FluxErp\\Models\\');
        $faker = Factory::create();

        foreach ($models as $model) {
            if (! in_array(InteractsWithMedia::class, class_uses($model))) {
                continue;
            }

            $registeredMediaCollections = $model::first()?->getRegisteredMediaCollections()
                ->pluck('name')
                ->toArray();

            $records = $model::all();

            foreach ($records as $record) {
                if ($registeredMediaCollections) {
                    foreach ($registeredMediaCollections as $registeredMediaCollection) {
                        $record->addMedia(UploadedFile::fake()->image($faker->domainName()))->toMediaCollection($registeredMediaCollection);
                    }
                } else {
                    for ($i = 0; $i < rand(0, 4); $i++) {
                        $record->addMedia(UploadedFile::fake()->image($faker->domainName()))
                            ->toMediaCollection(implode('.', $faker->words(rand(1, 4))));
                    }
                }
            }
        }
    }
}
