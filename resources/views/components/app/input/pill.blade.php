<div
    class="w-full"
    @if($attributes->has('wire:model.live')) x-data="{ content: @entangle($attributes['wire:model.live']).live }" @endif
>
    <div
        contenteditable="true"
        data-placeholder="{{ $placeholder ?? '' }}"
        @if($attributes->has('wire:model.live'))  x-on:keyup="content = $event.target.innerText" @endif
        {{ $attributes->whereDoesntStartWith('wire:model.live')->merge(['class' => 'truncate !leading-10 pl-6 inline-block align-middle before:text-portal-font-color empty:before:content-[attr(data-placeholder)] bg-white w-full h-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 px-4 rounded-full']) }}
    >
        {{ $slot }}
    </div>
</div>

<x-input
    class="before:text-portal-font-color block inline-block h-10 w-full truncate rounded-full border-gray-300 bg-white px-4 pl-6 align-middle !leading-10 shadow-sm empty:before:content-[attr(data-placeholder)] focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
/>
