<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Media;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads as WithFileUploadsBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait WithFileUploads
{
    use WithFileUploadsBase {
        WithFileUploadsBase::_removeUpload as parentRemoveUpload;
    }

    public array $filesArray = [];

    public string $collection = '';

    public function download(Media $mediaItem): false|BinaryFileResponse
    {
        if (! file_exists($mediaItem->getPath())) {
            if (method_exists($this, 'notification')) {
                $this->notification()->error(__('File not found!'));
            }

            return false;
        }

        return response()->download($mediaItem->getPath(), $mediaItem->name);
    }

    public function downloadCollection(string $collection): BinaryFileResponse
    {
        $media = Media::query()
            ->where('collection_name', 'like', $collection . '%')
            ->get();

        // add files to a zip file
        $zip = new \ZipArchive();
        $zipFileName = explode('.', $collection);
        $zipFileName = array_pop($zipFileName);
        $zipFileName = $zipFileName . '.zip';

        $zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($media as $file) {
            /** @var Media $file */
            if (! file_exists($file->getPath())) {
                continue;
            }

            if ($collection === $file->collection_name) {
                $relativePath = $file->name;
            } else {
                $collectionName = Str::remove($collection . '.', $file->collection_name);
                $relativePath = explode('.', $collectionName);
                $relativePath[] = $file->name;
                $relativePath = implode(DIRECTORY_SEPARATOR, $relativePath);
            }

            $zip->addFile($file->getPath(), $relativePath);
        }
        $zip->close();

        // download zip file
        return response()->download($zipFileName)->deleteFileAfterSend(true);
    }

    public function removeUpload(string $name, int $index): void
    {
        $this->parentRemoveUpload($name, $this->filesArray[$index]['key']);
        unset($this->filesArray[$index]);

        $this->skipRender();
    }

    public function prepareForMediaLibrary(string $name, int $modelId = null, string $modelType = null): void
    {
        $this->filesArray = [];
        $property = $this->getPropertyValue($name);
        $property = ! is_array($property) ? [$property] : $property;

        $modelId = $modelId ?: $this->modelId ?? null;
        $modelType = $modelType ?: $this->modelType;

        $collection = $this->collection ?: 'default';

        foreach ($property as $file) {
            /** @var TemporaryUploadedFile $file */
            // fix suffix as livewire sometimes changes this
            $suffix = pathinfo($file->getClientOriginalName(), \PATHINFO_EXTENSION);

            $this->filesArray[] = [
                'key' => $file->getFilename(),
                'name' => $file->getClientOriginalName(),
                'file_name' => $file->getClientOriginalName(),
                'model_id' => $modelId,
                'model_type' => $modelType,
                'collection_name' => $collection,
                'media' => $file->getRealPath(),
            ];
        }
    }

    public function saveFileUploadsToMediaLibrary(string $name, int $modelId = null, string $modelType = null): array
    {
        $this->prepareForMediaLibrary($name, $modelId, $modelType);
        $response = [];

        foreach ($this->filesArray as $file) {
            $response[] = UploadMedia::make($file)
                ->checkPermission()
                ->validate()
                ->execute();
        }

        $this->filesArray = [];

        $this->cleanupOldUploads();

        return $response;
    }
}
