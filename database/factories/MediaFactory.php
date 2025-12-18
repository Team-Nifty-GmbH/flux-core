<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'collection_name' => 'default',
            'name' => $filename = fake()->word() . '.png',
            'file_name' => $filename,
            'mime_type' => 'image/png',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => fake()->numberBetween(1024, 5120),
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => ['thumb' => true],
            'responsive_images' => [],
        ];
    }
}
