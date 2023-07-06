<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateSettingRequest;
use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateSetting implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateSettingRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'setting.update';
    }

    public static function description(): string|null
    {
        return 'update setting';
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function execute(): Model
    {
        $setting = Setting::query()
            ->whereKey($this->data['id'])
            ->first();

        $setting->settings = (object) $this->data['settings'];
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
