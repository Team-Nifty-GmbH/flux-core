<?php

namespace FluxErp\Actions\Media;

use Carbon\Carbon;
use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeleteMedia implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:media,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'media.delete';
    }

    public static function description(): string|null
    {
        return 'delete media';
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function execute()
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
