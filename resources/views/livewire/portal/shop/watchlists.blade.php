<div class="flex flex-col gap-8" x-data="{showCart: null}">
    @forelse($carts as $cart)
        <livewire:portal.shop.watchlist-card :cart="$cart" :key="uniqid()" />
    @empty
        <div class="flex flex-col gap-8 justify-center text-gray-900 dark:text-gray-50">
            <div>
                <h1 class="pt-5 pb-10 text-5xl font-bold text-center">
                    {{ __('Your watchlist is empty') }}
                </h1>
            </div>
        </div>
    @endforelse
</div>
