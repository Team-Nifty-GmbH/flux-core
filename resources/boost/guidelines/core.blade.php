{{-- Flux ERP AI Development Guidelines --}}
{{-- Generated from flux-core package analysis --}}

## Flux ERP Core Development Standards ### 1. Resource Structure A resource
always consists of the following components: - **Migration**: Database schema
definition - **Model**: Extends FluxModel with all direct relations - **Form**:
Livewire Form extending FluxForm - **DataTable**: List view extending
BaseDataTable - **Actions**: Create/Update/Delete extending FluxAction -
**Rulesets**: Validation extending FluxRuleset ### 2. Model Implementation
Standards #### Base Class & Traits
<code-snippet name="Model Base Class & Traits" lang="php">
    <?php

namespace FluxErp\Models;

use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasAttributeTranslations;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use FluxErp\Contracts\HasMediaForeignKey;

class Product extends FluxModel implements HasMedia, HasMediaForeignKey, InteractsWithDataTables
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns,
        HasAttributeTranslations, HasClientAssignment, HasFrontendAttributes,
        HasPackageFactory, HasParentChildRelations, HasSerialNumberRange,
        HasTags, HasUserModification, HasUuid, InteractsWithMedia,
        Lockable, LogsActivity, SoftDeletes, Searchable;

    public static string $iconName = 'square-3-stack-3d';
    protected ?string $detailRouteName = 'products.id';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (! $product->product_number) {
                $product->getSerialNumber('product_number');
            }
        });
    }
}
</code-snippet>

#### Pivot Models
<code-snippet name="Pivot Model Structure" lang="php">
<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Pivots\FluxPivot;

class CustomPivot extends FluxPivot
{
    protected $primaryKey = 'pivot_id';
    protected $table = 'custom_pivot_table';
}
</code-snippet>

### 3. Form Implementation (Livewire)

#### Form Structure
<code-snippet name="Livewire Form Structure" lang="php">
<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use Livewire\Attributes\Locked;

class ProductForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;
    public ?string $product_number = null;
    public ?bool $is_active = true;
    public ?float $price = null;
    public array $categories = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateProduct::class,
            'update' => UpdateProduct::class,
            'delete' => DeleteProduct::class,
        ];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'product_number' => 'nullable|string|unique:products,product_number,' . $this->id,
            'is_active' => 'boolean',
            'price' => 'nullable|numeric|min:0',
        ];
    }
}
</code-snippet>

### 4. DataTable Implementation

#### Base DataTable vs. HumanResources Component Pattern

**Important Separation:**
- **Base DataTable** (in `src/Livewire/DataTables/`): Only Model + enabledCols
- **HumanResources Component** (in `src/Livewire/HumanResources/`): Complete UI with Forms + Actions

<code-snippet name="DataTable Pattern - Base vs Full Component" lang="php">
<?php
// Base DataTable - Minimal
namespace FluxErp\Livewire\DataTables;

class EmployeeDayList extends BaseDataTable
{
    public string $model = EmployeeDay::class;

    public array $enabledCols = [
        'employee.name',
        'date',
        'target_hours',
        'actual_hours',
    ];

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with(['employee'])
            ->orderBy('date', 'desc');
    }
}

// HumanResources Component - Complete
namespace FluxErp\Livewire\HumanResources;

class EmployeeDays extends EmployeeDayList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public EmployeeDayForm $employeeDayForm;

    protected function getTableActions(): array
    {
        return [
            'create' => true,
            DataTableButton::make()
                ->text(__('Close Selected Days'))
                ->wireClick('closeSelectedDays'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            'edit' => true,
            'delete' => true,
        ];
    }
}
</code-snippet>

#### DataTable Structure
<code-snippet name="DataTable Structure with Formatters" lang="php">
<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Product;

class ProductList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'product_number',
        'is_active',
        'categories.name',
        'price',
    ];

    public array $formatters = [
        'product_image' => 'image',
        'price' => 'currency',
        'is_active' => 'boolean',
    ];

    protected string $model = Product::class;

    protected function getLeftAppends(): array
    {
        return [
            'name' => [
                'product_image',
            ],
        ];
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['product_image'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
</code-snippet>

### 5. Action Implementation

#### Action Structure
<code-snippet name="Action with Transaction & Relations" lang="php">
<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Models\Product;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Product\CreateProductRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateProduct extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductRuleset::class;
    }

    public function performAction(): Product
    {
        $productOptions = Arr::pull($this->data, 'product_options', []);
        $prices = Arr::pull($this->data, 'prices', []);
        $tags = Arr::pull($this->data, 'tags', []);

        $product = DB::transaction(function () use ($productOptions, $prices, $tags) {
            $product = app(Product::class, ['attributes' => $this->data]);
            $product->save();

            if ($productOptions) {
                $product->productOptions()->attach($productOptions);
            }

            if ($tags) {
                $product->attachTags(
                    resolve_static(Tag::class, 'query')
                        ->whereIntegerInRaw('id', $tags)
                        ->get()
                );
            }

            if ($prices && resolve_static(CreatePrice::class, 'canPerformAction', [false])) {
                foreach ($prices as $price) {
                    $price['product_id'] = $product->id;
                    CreatePrice::make($price)->validate()->execute();
                }
            }

            return $product;
        });

        return $product->refresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['product_type'] ??= data_get(ProductType::getDefault(), 'type');
    }
}
</code-snippet>

### 6. Ruleset Implementation

#### Ruleset Structure
<code-snippet name="Ruleset with ModelExists Rule" lang="php">
<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Models\Unit;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateProductRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'product_number' => 'nullable|string|unique:products,product_number',
            'vat_rate_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'unit_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'is_active' => 'boolean',
            'price' => 'nullable|numeric|min:0',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ClientRuleset::class, 'getRules'),
            resolve_static(CategoryRuleset::class, 'getRules'),
        );
    }
}
</code-snippet>

### 7. Migration Standards

#### Field Order
<code-snippet name="Migration Field Order Standard" lang="php">
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // 1. Primary Keys
            $table->id();
            $table->uuid()->unique();

            // 2. Foreign Keys (alphabetically)
            $table->foreignId('client_id')->nullable()->constrained();
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->foreignId('vat_rate_id')->constrained();

            // 3. Content Fields (logical order)
            $table->string('name');
            $table->string('product_number')->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->json('meta_data')->nullable();

            // 4. Boolean Fields (alphabetically)
            $table->boolean('has_serial_numbers')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_service')->default(false);

            // 5. Timestamps & User Tracking
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users');
        });
    }
};
</code-snippet>

## Blade File Patterns

### Main Structure for Detail Views

<code-snippet name="Detail View Structure with Edit Mode" lang="blade">
@verbatim
<div id="product" class="min-h-full">
    {{-- Modal Includes --}}
    {{ $this->renderCreateDocumentsModal() }}

    <main class="py-10">
        {{-- Header Section with Avatar/Title/Actions --}}
        <div class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8">
            <div class="flex items-center space-x-5">
                @section('product.title')
                    <x-avatar xl :image="$product->avatar_url" />
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                            <span x-text="$wire.product.name"></span>
                            <span class="opacity-40" x-text="$wire.product.product_number"></span>
                        </h1>
                    </div>
                @show
            </div>

            {{-- Action Buttons --}}
            <div class="mt-6 flex flex-col-reverse justify-stretch gap-2">
                @canAction(\FluxErp\Actions\Product\UpdateProduct::class)
                    <div x-cloak x-show="$wire.edit" class="flex gap-x-2">
                        <x-button
                            color="secondary"
                            :text="__('Cancel')"
                            x-on:click="$wire.edit = false; $wire.resetProduct()"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            x-on:click="$wire.save()"
                        />
                    </div>
                    <div x-cloak x-show="! $wire.edit">
                        <x-button
                            color="indigo"
                            :text="__('Edit')"
                            x-on:click="$wire.edit = true"
                        />
                    </div>
                @endcanAction

                @canAction(\FluxErp\Actions\Product\DeleteProduct::class)
                    <x-button
                        color="red"
                        :text="__('Delete')"
                        wire:click="delete"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Product')]) }}"
                    />
                @endcanAction
            </div>
        </div>

        {{-- Tabs --}}
        <x-flux::tabs wire:model.live="tab" :$tabs wire:ignore />
    </main>
</div>
@endverbatim
</code-snippet>

### Alpine.js Data Structure

<code-snippet name="Alpine.js Component with Init & Methods" lang="blade">
@verbatim
<div x-data="{
    {{-- Initialization --}}
    init() {
        document.body.dataset.currencyCode = $wire.order.currency.iso
    },

    {{-- Local State --}}
    showDetails: false,
    edit: false,

    {{-- Arrays/Collections --}}
    orderPositions: [],

    {{-- Formatter/Helper --}}
    formatter: @js(resolve_static(\FluxErp\Models\Order::class, 'typeScriptAttributes')),

    {{-- Methods --}}
    updateContactId(id) {
        $tallstackuiSelect('invoice-address-id').mergeRequestParams({
            where: [['contact_id', '=', id]],
        })
    }
}">
    {{-- Component Content --}}
</div>
@endverbatim
</code-snippet>

### Form Layout Pattern

<code-snippet name="Form Layout with Grid & Sections" lang="blade">
@verbatim
<x-card>
    @section('product-edit')
    <form class="space-y-5">
        {{-- Grid Layout for Form Fields --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @section('product-edit.basic')
                <x-input
                    :label="__('Name')"
                    wire:model="productForm.name"
                    required
                />
                <x-input
                    :label="__('Product Number')"
                    wire:model="productForm.product_number"
                />
                <x-number
                    :label="__('Price')"
                    wire:model="productForm.price"
                    :prefix="$currency->symbol"
                    min="0"
                    step="0.01"
                />
                <x-toggle
                    :label="__('Active')"
                    wire:model="productForm.is_active"
                />
            @show
        </div>

        <hr />

        {{-- Select Components --}}
        <div class="space-y-4">
            @section('product-edit.selects')
                <x-select.styled
                    wire:model="productForm.vat_rate_id"
                    :label="__('VAT Rate')"
                    required
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\VatRate::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name', 'rate_percent'],
                            'where' => [
                                ['is_active', '=', true]
                            ]
                        ]
                    ]"
                />
            @show
        </div>
    </form>
    @show
</x-card>
@endverbatim
</code-snippet>

### Modal Pattern

<code-snippet name="Modal with Form & Footer Buttons" lang="blade">
@verbatim
<x-modal :id="$form->modalName()" size="xl">
    <x-slot:title>
        {{ __('Create Product') }}
    </x-slot:title>

    <div class="space-y-4">
        <x-input
            :label="__('Name')"
            wire:model="form.name"
            required
        />

        <x-select.styled
            wire:model="form.category_id"
            :label="__('Category')"
            select="label:name|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Category::class),
                'method' => 'POST',
                'params' => [
                    'searchFields' => ['name'],
                    'with' => 'parent'
                ]
            ]"
        />
    </div>

    <x-slot:footer>
        <x-button
            :text="__('Cancel')"
            color="secondary"
            flat
            x-on:click="$modalClose('{{ $form->modalName() }}')"
        />
        <x-button
            :text="__('Save')"
            color="primary"
            wire:click="save"
            loading
        />
    </x-slot:footer>
</x-modal>
@endverbatim
</code-snippet>

### Dynamic Table Pattern

<code-snippet name="Dynamic Table with Alpine.js Loop" lang="blade">
@verbatim
<x-flux::table>
    <x-slot:header>
        <x-flux::table.head-cell>{{ __('Name') }}</x-flux::table.head-cell>
        <x-flux::table.head-cell>{{ __('Price') }}</x-flux::table.head-cell>
        <x-flux::table.head-cell>{{ __('Actions') }}</x-flux::table.head-cell>
    </x-slot>

    {{-- Alpine.js Template Loop --}}
    <template x-for="(item, index) in $wire.items">
        <tr>
            <td>
                <x-input x-model="item.name" />
            </td>
            <td>
                <x-number x-model="item.price" />
            </td>
            <td>
                <x-button.circle
                    icon="trash"
                    color="red"
                    sm
                    x-on:click="$wire.items.splice(index, 1)"
                />
            </td>
        </tr>
    </template>
</x-flux::table>

{{-- Add Button --}}
<div class="flex w-full justify-center pt-4">
    <x-button
        color="indigo"
        :text="__('Add Item')"
        x-on:click="$wire.items.push({ name: '', price: 0 })"
    />
</div>
@endverbatim
</code-snippet>

### Tabs Navigation Pattern

<code-snippet name="Tabs with Alpine.js State" lang="blade">
@verbatim
<div class="border-b border-gray-200" x-data="{ active: 'general' }">
    <nav class="-mb-px mt-2 flex space-x-8 pb-5">
        <div
            x-on:click="active = 'general'"
            x-bind:class="active === 'general' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
            class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
        >
            {{ __('General') }}
        </div>
        <div
            x-on:click="active = 'prices'"
            x-bind:class="active === 'prices' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
            class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
        >
            {{ __('Prices') }}
        </div>
    </nav>

    {{-- Tab Contents --}}
    <div x-show="active === 'general'" x-cloak>
        {{-- General Tab Content --}}
    </div>

    <div x-show="active === 'prices'" x-cloak>
        {{-- Prices Tab Content --}}
    </div>
</div>
@endverbatim
</code-snippet>

## TallStackUI Component Reference

### Input Components

<code-snippet name="TallStackUI Input Components" lang="blade">
@verbatim
{{-- Text Input --}}
<x-input wire:model="form.name" :label="__('Name')" />

{{-- Number Input --}}
<x-number wire:model="form.amount" :label="__('Amount')" min="0" step="0.01" />

{{-- Date Input --}}
<x-date wire:model="form.date" :label="__('Date')" :without-time="true" />

{{-- Textarea --}}
<x-textarea wire:model="form.description" :label="__('Description')" rows="4" />

{{-- Password --}}
<x-password wire:model="form.password" :label="__('Password')" />

{{-- Toggle (NEVER x-checkbox!) --}}
<x-toggle wire:model="form.is_active" :label="__('Active')" />

{{-- Color Picker --}}
<x-color wire:model="form.color" :label="__('Color')" />
@endverbatim
</code-snippet>

### Select Components

<code-snippet name="TallStackUI Select Components" lang="blade">
@verbatim
{{-- Static Select --}}
<x-select.native
    wire:model="form.type"
    :label="__('Type')"
    :options="$types"
    option-label="name"
    option-value="id"
/>

{{-- Async Select with Search --}}
<x-select.styled
    wire:model="form.address_id"
    :label="__('Address')"
    select="value:id|label:label"
    unfiltered
    :request="[
        'url' => route('search', \FluxErp\Models\Address::class),
        'method' => 'POST',
        'params' => [
            'searchFields' => ['name', 'street'],
            'where' => [
                ['is_active', '=', true]
            ],
            'with' => 'contact.media'
        ]
    ]"
/>

{{-- Multiple Select --}}
<x-select.styled
    wire:model="form.categories"
    :label="__('Categories')"
    multiple
    select="label:name|value:id"
    :options="$categories"
/>
@endverbatim
</code-snippet>

### Button Components

<code-snippet name="TallStackUI Button Components" lang="blade">
@verbatim
{{-- Standard Button --}}
<x-button :text="__('Save')" color="primary" icon="check" />

{{-- Circle Button --}}
<x-button.circle icon="trash" color="red" sm />

{{-- Button with Loading --}}
<x-button :text="__('Submit')" wire:click="submit" loading />

{{-- Button with Confirm --}}
<x-button
    :text="__('Delete')"
    color="red"
    wire:click="delete"
    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Product')]) }}"
/>
@endverbatim
</code-snippet>

### Event Handling Patterns

<code-snippet name="Event Handling Patterns" lang="blade">
@verbatim
{{-- Livewire Events --}}
<x-button wire:click="save" />
<x-button wire:click="save().then(() => { $modalClose('modal-id') })" />

{{-- Alpine Events --}}
<x-button x-on:click="edit = !edit" />
<x-button x-on:click="$wire.save().then(() => { edit = false })" />

{{-- Modal Events --}}
<x-button x-on:click="$modalOpen('modal-id')" />
<x-button x-on:click="$modalClose('modal-id')" />

{{-- Select Events --}}
<x-select.styled
    x-on:select="updateContactId($event.detail.select.contact_id)"
    x-on:select="$tallstackuiSelect('other-select').mergeRequestParams({ where: [['field', '=', $event.detail.select.value]] })"
/>
@endverbatim
</code-snippet>

## Wire Model Binding Patterns

<code-snippet name="Wire Model Binding Options" lang="blade">
@verbatim
{{-- Direct binding --}}
<x-input wire:model="form.name" />

{{-- Live binding (updates on every keystroke) --}}
<x-input wire:model.live="form.name" />

{{-- Lazy binding (updates on blur) --}}
<x-input wire:model.lazy="form.name" />

{{-- Debounced binding --}}
<x-input wire:model.live.debounce.500ms="search" />

{{-- Alpine binding to Livewire property --}}
<x-input x-model="$wire.form.name" />
@endverbatim
</code-snippet>

## Conditional Rendering

<code-snippet name="Conditional Rendering Patterns" lang="blade">
@verbatim
{{-- Use x-cloak to prevent flicker --}}
<div x-show="edit" x-cloak>
    {{-- Content only visible when edit = true --}}
</div>

{{-- With Transition --}}
<div x-show="open" x-transition>
    {{-- Content with Fade Transition --}}
</div>

{{-- With x-collapse for Height Animation --}}
<div x-show="expanded" x-collapse>
    {{-- Content with Collapse Animation --}}
</div>

{{-- Blade Conditionals --}}
@canAction(\FluxErp\Actions\Product\UpdateProduct::class)
    {{-- Only visible with Permission --}}
@endcanAction
@endverbatim
</code-snippet>

## Quick Reference Checkliste

### Creating a New Resource:
- [ ] Migration with correct field order
- [ ] Model extends FluxModel with all Relations and Imports
- [ ] Register Model in morphMap
- [ ] Form extends FluxForm with #[Locked] property on ID
- [ ] DataTable extends BaseDataTable
- [ ] Create/Update/Delete Actions with all Imports
- [ ] Rulesets with ModelExists validation
- [ ] Blade Views with TallStackUI Components
- [ ] Write Smoke Tests
- [ ] German translations in de.json

### Component Usage:
- [ ] ONLY use TallStackUI Components
- [ ] x-button with :text property
- [ ] x-select.styled for async selects
- [ ] wire:flux-confirm for dialogs
- [ ] Use x-cloak with x-show

### Code Quality:
- [ ] All use statements in PHP files
- [ ] resolve_static() for static calls
- [ ] bccomp() for number comparisons
- [ ] Use data_get() helper
- [ ] #[Renderless] for non-UI methods
- [ ] wire:navigate for internal links
- [ ] DB::transaction() for atomic operations

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>

</code-snippet>
