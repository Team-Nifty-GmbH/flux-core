@props(['client'])
<div
    draggable="false"
    class="w-fit">
    <div>
        {{ $client->postal_address_one_line }}
    </div>
    <div class="h-[1px] bg-black"></div>
</div>
