<div class="flex flex-col gap-4">
    <x-flux::editor
        x-modelable="content"
        x-model="$wire.$parent.order.header"
        :label="__('Header')"
    />
    <x-flux::editor
        x-modelable="content"
        x-model="$wire.$parent.order.footer"
        :label="__('Footer')"
    />
</div>
