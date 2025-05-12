<?php

namespace FluxErp\Tests\Feature\Database\Factories;

use FluxErp\Models\ProductProperty;
use FluxErp\Tests\Feature\BaseSetup;

class ProductPropertyFactoryTest extends BaseSetup
{
    public function test_it_can_override_fields(): void
    {
        $property = ProductProperty::factory()->create([
            'name' => 'weight_grams',
        ]);

        $this->assertEquals('weight_grams', $property->name);
    }

    public function test_it_creates_a_valid_product_property(): void
    {
        $allowed = ['dimension_height', 'dimension_width', 'dimension_length', 'weight_grams'];
        $property = ProductProperty::factory()->create();

        $this->assertInstanceOf(ProductProperty::class, $property);
        $this->assertNotNull($property->name);
        $this->assertContains($property->name, $allowed);
    }

    public function test_make_does_not_persist_but_create_does(): void
    {
        $property = ProductProperty::factory()->make();
        $this->assertInstanceOf(ProductProperty::class, $property);
        $this->assertFalse($property->exists);

        $created = ProductProperty::factory()->create();
        $this->assertTrue($created->exists);
        $this->assertDatabaseHas('product_properties', [
            'id' => $created->id,
        ]);
    }

    public function test_multiple_instances_have_valid_values(): void
    {
        $allowed = ['dimension_height', 'dimension_width', 'dimension_length', 'weight_grams'];
        $properties = ProductProperty::factory()->count(10)->create();

        $this->assertCount(10, $properties);

        foreach ($properties as $property) {
            $this->assertContains($property->name, $allowed);
            $this->assertTrue($property->exists);
        }
    }
}
