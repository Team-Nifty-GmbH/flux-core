<div class="flex flex-col gap-8" x-data="{ showCart: null }">
    @forelse ($carts as $cart)
        <livewire:portal.shop.watchlist-card :cart="$cart" :key="uniqid()" />
    @empty
        <div
            class="flex flex-col justify-center gap-8 text-gray-900 dark:text-gray-50"
        >
            <div>
                <h1 class="pt-5 pb-10 text-center text-5xl font-bold">
                    {{ __('Your watchlist is empty') }}
                </h1>
            </div>
        </div>
    @endforelse
</div>
