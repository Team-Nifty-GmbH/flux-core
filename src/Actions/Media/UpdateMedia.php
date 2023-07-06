<?php

namespace FluxErp\Actions\Media;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateMediaRequest;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateMedia implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateMediaRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'media.update';
    }

    public static function description(): string|null
    {
        return 'update media';
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function execute(): Model
    {
        $media = Media::query()
            ->whereKey($this->data['id'])
            ->first();

        $media->fill($this->data);
        $media->save();

        return $media->withoutRelations();
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
