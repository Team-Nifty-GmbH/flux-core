<div>
    <x-select
        :label="__('Filter by path')"
        :options="$slugs"
        wire:model="filterSlug"
        multiselect
    />
    @foreach($attachments as $media)
        <x-features.media.file
            icon=""
            class="border-portal-font-color text-portal-font-color border-b"
            :filename="$media['file_name']"
            :slug="$media['slug']"
        >
            <x-slot name="buttons">
                <div wire:click="removeUpload('attachments', 'test')" class="cursor-pointer text-xs font-bold">
                    <x-heroicons name="arrow-up-right" class="h-4 w-4" />
                    {{ __('Open') }}
                </div>
                <div wire:click="download({{ $media['id'] }})" class="cursor-pointer text-xs font-bold">
                    <x-heroicons name="arrow-down" class="h-4 w-4" />
                    {{ __('Download') }}
                </div>
            </x-slot>
        </x-features.media.file>
    @endforeach
</div>
