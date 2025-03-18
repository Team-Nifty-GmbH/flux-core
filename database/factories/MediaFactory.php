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
            'uuid' => $this->faker->uuid(),
            'collection_name' => 'default',
            'name' => $filename = $this->faker->word() . '.png',
            'file_name' => $filename,
            'mime_type' => 'image/png',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => $this->faker->numberBetween(1024, 5120),
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => ['thumb' => true],
            'responsive_images' => [],
        ];
    }
}
