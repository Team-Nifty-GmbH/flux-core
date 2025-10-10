<?php

namespace FluxErp\Traits\Livewire\PrintLayout;

use Error;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\PrintLayout;

trait MediaHandler
{
    // used to add media on user submit (user added new media on front-end - not immediately reflected on back-end)
    // media are stored temporarily on front-end until user submits
    // previous state is preserved if user decides to cancel the operation
    /**
     * @param  array  $rootElement  (by reference) - related to header, footer or first_page_header
     *                              which may contain temporaryMedia files related to it
     * @param  array  $temporaryMedia  - array of all temporary media uploaded on front-end
     * @param  int  $modelId  - id of the PrintLayout model to which media will be related
     */
    public function addMedia(array &$rootElement, array $temporaryMedia, int $modelId): void
    {
        if ($rootElement['temporaryMedia']) {
            foreach ($rootElement['temporaryMedia'] as $imagePosition) {
                $index = array_search($imagePosition['name'], array_map(fn ($item) => $item->getFilename(), $temporaryMedia));
                if ($index !== false) {
                    // save temporary images to Media
                    $tempMedia = $temporaryMedia[$index];
                    $media = UploadMedia::make([
                        'media' => $tempMedia,
                        'model_id' => $modelId,
                        'model_type' => morph_alias(PrintLayout::class),
                        'collection_name' => 'print_layout',
                    ])->checkPermission()
                        ->validate()
                        ->execute();
                    // mutate rootElement data to match media
                    unset($imagePosition['name']);
                    $imagePosition['id'] = $media->id;
                    $imagePosition['src'] = $media->original_url;
                    // add media to rootElement before submiting to db
                    $rootElement['media'][] = $imagePosition;
                } else {
                    throw new Error('Temporary image not found in temporary media - mismatch between root temporary media and temporary media');
                }
            }
        }
        // remove meta data regarding position of temporary images - no need to store it in db
        unset($rootElement['temporaryMedia']);
    }

    // used to sync media between front-end and back-end on user submit (user deleted the media on front-end)
    // deleting media on frond-end will not be immediately reflected on back-end
    // in order to preserve previous state if user decides to cancel the operation
    /**
     * @param  array  $rootElementMedia  - related to header, footer or first_page_header
     *                                   containing latest media snapshot from front-end
     * @param  array  $dbSnapshot  - snapshot of media related to header, footer or first_page_header
     *                             before user started editing it on front-end
     */
    public function syncMedia(array $rootElementMedia, array $dbSnapshot): void
    {
        $diff = array_diff(array_column($dbSnapshot, 'id'), array_column($rootElementMedia, 'id'));
        if ($diff) {
            foreach ($diff as $mediaId) {
                DeleteMedia::make([
                    'id' => $mediaId,
                ])->checkPermission()
                    ->validate()
                    ->execute();
            }
        }
    }
}
