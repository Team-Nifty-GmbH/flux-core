@php
    use Illuminate\Support\Arr;

    $path = flux_path('resources/views/printing');
    $dirs = array_filter(scandir($path), fn ($item) => ! str_starts_with($item, '.'));

    $printing = Arr::collapse(
        array_map(function ($dir) {
            $fileNames = array_filter(scandir(flux_path('resources/views/printing') . '/' . $dir), fn ($item) => ! str_starts_with($item, '.'));
            // remove extension from file names
            $fileNames = array_map(fn ($item) => str_replace('.blade.php', '', $item), $fileNames);
            return [$dir => array_values($fileNames)];
        }, $dirs),
    );
@endphp

<div>
    @foreach ($printing as $folder => $files)
        {{-- TODO: remove continut when other layout are build --}}
        @continue($folder !== 'order')
        <div
            class="mb-4 flex w-full flex-col rounded-lg border border-gray-300"
        >
            <div class="flex h-10 w-full items-center bg-gray-200 p-4">
                {{ __($folder) }}
            </div>
            @foreach ($files as $index => $file)
                <div
                    class="{{ count($files) > 1 && $index < count($files) - 1 ? 'border-b border-gray-300' : '' }} flex min-h-10 w-full items-center justify-between p-4"
                >
                    <div>{{ __($file) }}</div>
                    <x-button
                        href="{{ route('print-layout-editor',
                                [
                                    'layoutModel'=>$folder,
                                    'name' => $file
                                    ]) }}"
                        text="Edit"
                    />
                </div>
            @endforeach
        </div>
    @endforeach
</div>
