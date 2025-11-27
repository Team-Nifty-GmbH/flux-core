<div class="flex flex-col gap-1.5">
    <x-flux::editor
        x-modelable="content"
        x-model="$wire.$parent.order.header"
        scope="order"
        :label="__('Header')"
    />
    <x-flux::editor
        x-modelable="content"
        x-model="$wire.$parent.order.footer"
        scope="order"
        :label="__('Footer')"
    />
</div>
