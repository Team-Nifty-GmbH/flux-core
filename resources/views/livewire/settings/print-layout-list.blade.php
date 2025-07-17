@php
    use Illuminate\Support\Arr;

    $path = flux_path('resources/views/printing');
    $dirs = array_filter(scandir($path), fn($item) => !str_starts_with($item, '.'));

    $printing  = Arr::collapse(array_map(function ($dir) {
        $fileNames = array_filter(scandir(flux_path('resources/views/printing') . "/" . $dir),fn($item) => !str_starts_with($item, '.'));
        // remove extension from file names
        $fileNames = array_map(fn($item) => str_replace('.blade.php', '', $item), $fileNames);
        return [$dir => array_values($fileNames)];
    }, $dirs));
@endphp
<div>

@foreach($printing as $folder => $files)
    <div class="flex flex-col mb-4 w-full border border-gray-300 rounded-lg">
        <div class="h-10 flex items-center p-4 bg-gray-200 w-full">{{ $folder }}</div>
        @foreach($files as $index => $file)
            <div class="min-h-10 w-full flex justify-between items-center p-4 {{ count($files) > 1 && $index < count($files) -1 ? 'border-b border-gray-300' : ''}}">
                <div>{{ $file }}</div>
                <x-button
                    href="{{ route('print-layout-editor',
                                [
                                    'type'=>$folder,
                                    'name' => $file
                                    ]) }}"
                    text="Edit"/>
            </div>
        @endforeach
    </div>
@endforeach
</div>
