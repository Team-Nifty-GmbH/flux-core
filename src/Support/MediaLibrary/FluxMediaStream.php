<?php

namespace FluxErp\Support\MediaLibrary;

use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use FluxErp\Traits\Makeable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Spatie\MediaLibrary\Support\MediaStream;
use ZipStream\ZipStream;

class FluxMediaStream extends MediaStream
{
    use Conditionable, Makeable;

    protected Collection $mediaFolders;

    protected array $nameCounters = [];

    public function __construct(string $zipName)
    {
        parent::__construct($zipName);

        $this->mediaFolders = collect();
    }

    public static function create(string $zipName): static
    {
        return static::make($zipName);
    }

    public function addMediaFolder(...$mediaFolders): static
    {
        collect($mediaFolders)
            ->flatMap(function ($item) {
                if ($item instanceof MediaFolder) {
                    return [$item];
                }

                if ($item instanceof Collection) {
                    return $item->reduce(function (array $carry, MediaFolder $mediaFolder) {
                        $carry[] = $mediaFolder;

                        return $carry;
                    }, []);
                }

                return $item;
            })
            ->each(fn (MediaFolder $mediaFolder) => $this->mediaFolders->push($mediaFolder));

        return $this;
    }

    public function getZipStream(): ZipStream
    {
        $this->zipOptions['outputName'] = $this->zipName;
        $zip = new ZipStream(...$this->zipOptions);

        $createRootFolders = $this->mediaFolders->count() > 1;
        foreach ($this->mediaFolders as $mediaFolder) {
            if ($createRootFolders) {
                $mediaFolderName = trim($mediaFolder->name) . '/';
                $zip->addDirectory($mediaFolderName);
            }

            $this->addFilesToZipStream($zip, $mediaFolder, $createRootFolders ? $mediaFolderName : null);
        }

        $this->getZipStreamContents()->each(function (array $mediaInZip) use ($zip): void {
            $stream = $mediaInZip['media']->stream();

            $zip->addFileFromStream($mediaInZip['fileNameInZip'], $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        $zip->finish();

        return $zip;
    }

    protected function addFilesToZipStream(ZipStream $zipStream, MediaFolder $folder, ?string $parent = null): void
    {
        $folder->media()
            ->get()
            ->each(function (Media $media) use ($zipStream, $parent): void {
                $stream = $media->stream();

                $collection = collect([$media]);

                $fileNameInZip = $parent . $this->getZipFileNamePrefix($collection, 0)
                    . $this->getFileNameWithSuffix($collection, 0, $parent);

                $zipStream->addFileFromStream($fileNameInZip, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            });

        if (! $folder->relationLoaded('children')) {
            return;
        }

        foreach ($folder->children as $child) {
            $this->addFilesToZipStream($zipStream, $child, $this->getFolderName($child, $parent));
        }
    }

    protected function getFileNameWithSuffix(Collection $mediaItems, int $currentIndex, ?string $parent = null): string
    {
        $fileName = $mediaItems[$currentIndex]->getDownloadFilename();

        $prefix = $this->getZipFileNamePrefix($mediaItems, $currentIndex);
        $key = $parent . $prefix . $fileName;

        $count = $this->nameCounters[$key] ?? 0;
        $this->nameCounters[$key] = $count + 1;

        if ($count === 0) {
            return $fileName;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);

        return "{$fileNameWithoutExtension} ({$count}).{$extension}";
    }

    protected function getFolderName(MediaFolder $folder, ?string $parent = null): string
    {
        $folderName = $parent . trim($folder->name);

        $count = $this->nameCounters[$folderName] ?? 0;
        $this->nameCounters[$folderName] = $count + 1;

        if ($count === 0) {
            return $folderName . '/';
        }

        return $folderName . ' (' . $count . ')/';
    }
}
