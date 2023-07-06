<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateContactOptionRequest;
use FluxErp\Models\ContactOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateContactOption implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateContactOptionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'contact-option.update';
    }

    public static function description(): string|null
    {
        return 'update contact option';
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function execute(): Model
    {
        $contactOption = ContactOption::query()
            ->whereKey($this->data['id'])
            ->first();

        $contactOption->fill($this->data);
        $contactOption->save();

        return $contactOption->withoutRelations()->fresh();
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
