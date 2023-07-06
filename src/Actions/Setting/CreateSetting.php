<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateSettingRequest;
use FluxErp\Models\Setting;
use Illuminate\Support\Facades\Validator;

class CreateSetting implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateSettingRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'setting.create';
    }

    public static function description(): string|null
    {
        return 'create setting';
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function execute(): Setting
    {
        $setting = new Setting($this->data);
        $setting->save();

        return $setting;
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
