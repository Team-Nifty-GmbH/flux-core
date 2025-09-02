<?php

namespace FluxErp\Traits\Livewire\PrintLayout;

// used to sync media between front-end and back-end on user submit (user deleted the media on front-end)
// deleting media on frond-end will not be immediately reflected on back-end
// in order to preserve previous state if user decides to cancel the operation

use FluxErp\Actions\Media\DeleteMedia;

trait SyncMedia
{
    public function syncMedia(array $rootElementMedia, array $dbSnapshot):void {
        $diff =  array_diff(array_column($dbSnapshot,'id'),array_column($rootElementMedia,'id'));
        if($diff) {
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
