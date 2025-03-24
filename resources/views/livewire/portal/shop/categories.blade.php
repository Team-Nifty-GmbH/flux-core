<x-card :header="__('Categories')">
    <div
        class="flex flex-col gap-1.5"
        x-data="{
            selectCategory(id = null, path = null) {
                if (id === null) {
                    this.open = []
                } else {
                    this.open.push(path)
                    this.open = this.open.filter(
                        (value, index, self) => self.indexOf(value) === index,
                    )
                }
                $wire.$parent.set('category', id)
                $wire.$parent.setPage(1)
            },
            open: [],
        }"
    >
        <x-portal.shop.category
            :category="new \Illuminate\Support\Fluent(['id' => null, 'name' => __('All products')])"
        />
        @foreach ($this->categories as $category)
            <x-portal.shop.category :category="$category" />
        @endforeach
    </div>
</x-card>
