<?php

namespace FluxErp\Actions\Media;

use Carbon\Carbon;
use FluxErp\Actions\BaseAction;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeleteMedia extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:media,id',
        ];
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function execute(): ?bool
    {
        $mediaItem = Media::query()
            ->whereKey($this->data['id'])
            ->first();

        $attributes = $mediaItem->getAttributes();
        $attributes['deleted_at'] = Carbon::now()->toDateTimeString();
        $attributes['deleted_by'] = Auth::id();
        $message = 'File: \'' . $mediaItem->file_name . '\' deleted by user: \'' . Auth::id() . '\'';
        Log::notice($message, array_merge(['uuid' => $mediaItem->uuid], $attributes));

        return $mediaItem->delete();
    }
}
