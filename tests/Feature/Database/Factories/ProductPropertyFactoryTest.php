<?php

namespace FluxErp\Tests\Feature\Database\Factories;

use FluxErp\Tests\Feature\BaseSetup;
use FluxErp\Models\ProductProperty;

class ProductPropertyFactoryTest extends BaseSetup
{

    public function test_it_creates_a_valid_product_property()
    {
        $property = ProductProperty::factory()->create();

        $this->assertInstanceOf(ProductProperty::class, $property);
        $this->assertNotNull($property->name);
        $this->assertContains($property->name, [
            'dimension_height',
            'dimension_width',
            'dimension_length',
            'weight_grams',
        ]);
    }

    public function test_it_can_override_fields()
    {
        $property = ProductProperty::factory()->create([
            'name' => 'weight_grams',
        ]);

        $this->assertEquals('weight_grams', $property->name);
    }
}
