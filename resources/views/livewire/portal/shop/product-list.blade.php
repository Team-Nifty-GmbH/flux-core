<div>
    <x-input
        type="search"
        class="w-full"
        :placeholder="__('Type to search for products…')"
        wire:model.live.debounce="search"
        class="mb-4"
    />
    <div class="flex flex-col gap-4 sm:flex-row">
        <div>
            <livewire:portal.shop.categories />
        </div>
        <div>
            <x-flux::spinner />
            <div>
                {{ $products->links() }}
            </div>
            @section('products')
            <div
                class="grid grid-cols-1 gap-4 pt-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5"
            >
                @forelse ($products as $product)
                    <livewire:portal.shop.product-list-card
                        :product="$product"
                        :key="$product['id']"
                    />
                @empty
                    <div class="text-secondary-400 text-center">
                        {{ __('No products found') }}
                    </div>
                @endforelse
            </div>
            @show
            <div class="pt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
