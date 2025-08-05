<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\UpdateProduct;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class ProductForm extends FluxForm
{
    public ?string $avatar_url = null;

    public ?float $basic_unit = null;

    public array $bundle_products = [];

    public ?string $bundle_type_enum = null;

    public array $categories = [];

    public ?int $children_count = null;

    public ?int $client_id = null;

    public array $clients = [];

    public ?int $cover_media_id = null;

    public ?string $customs_tariff_number = null;

    public ?string $description = null;

    public ?float $dimension_height_mm = null;

    public ?float $dimension_length_mm = null;

    public ?float $dimension_width_mm = null;

    public ?string $ean = null;

    public ?bool $has_serial_numbers = false;

    #[Locked]
    public ?int $id = null;

    public ?bool $is_active = true;

    public ?bool $is_active_export_to_web_shop = false;

    #[Locked]
    public ?bool $is_bundle = false;

    public ?bool $is_highlight = false;

    public ?bool $is_nos = false;

    public ?bool $is_service = false;

    public ?bool $is_shipping_free = false;

    public ?int $max_delivery_time = null;

    public ?float $max_purchase = null;

    public ?int $min_delivery_time = null;

    public ?float $min_purchase = null;

    public ?string $name = null;

    public ?array $parent = null;

    public ?int $parent_id = null;

    public ?string $posting_account = null;

    public array $prices = [];

    public array $product_cross_sellings = [];

    public ?string $product_number = null;

    public array $product_properties = [];

    public ?string $product_type = null;

    public ?float $purchase_steps = null;

    public ?int $purchase_unit_id = null;

    public ?int $reference_unit_id = null;

    public ?int $restock_time = null;

    public ?array $search_aliases = null;

    public ?float $selling_unit = null;

    public ?string $seo_keywords = null;

    public ?int $stock = null;

    public array $suppliers = [];

    public array $tags = [];

    public ?string $time_unit_enum = null;

    public ?int $unit_id = null;

    public ?array $vat_rate = null;

    public ?int $vat_rate_id = null;

    public ?float $warning_stock_amount = null;

    public ?float $weight_gram = null;

    public function fill($values): void
    {
        if ($values instanceof Model) {
            $values->loadMissing([
                'bundleProducts:id',
                'categories:id',
                'clients:id',
                'coverMedia',
                'parent',
                'productProperties:id,product_property_group_id,name,property_type_enum,product_product_property.value',
                'productProperties.productPropertyGroup:id,name',
                'suppliers:id,main_address_id,customer_number,' .
                    'product_supplier.contact_id,' .
                    'product_supplier.manufacturer_product_number,' .
                    'product_supplier.purchase_price',
                'suppliers.mainAddress:id,name',
                'tags:id',
                'vatRate:id,rate_percentage',
            ]);

            $values->append('avatar_url');
        }

        parent::fill($values);

        $this->categories = array_column($this->categories, 'id');
        $this->tags = array_column($this->tags, 'id');
        $this->clients = array_column($this->clients, 'id');
        $this->parent = $this->parent
            ? [
                'label' => $values->parent->getLabel(),
                'url' => $values->parent->getUrl(),
            ]
            : null;

        $this->bundle_products = array_map(function ($bundleProduct) {
            return [
                'id' => $bundleProduct['id'] ?? null,
                'count' => $bundleProduct['pivot']['count'] ?? 0,
            ];
        }, $this->bundle_products);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateProduct::class,
            'update' => UpdateProduct::class,
            'delete' => DeleteProduct::class,
        ];
    }
}
