<?php

namespace FluxErp\Actions\NotificationSetting;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateNotificationSettingsRequest;
use FluxErp\Http\Requests\UpdateUserNotificationSettingsRequest;
use FluxErp\Models\NotificationSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpdateNotificationSetting implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = array_merge(['is_anonymous' => false], $data);
        $this->rules = $this->data['is_anonymous'] ?
            (new UpdateNotificationSettingsRequest())->rules() : (new UpdateUserNotificationSettingsRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'notification-setting.update';
    }

    public static function description(): string|null
    {
        return 'update notification setting';
    }

    public static function models(): array
    {
        return [NotificationSetting::class];
    }

    public function execute(): Model
    {
        $notificationSetting = NotificationSetting::query()
            ->firstOrNew([
                'notifiable_type' => ! $this->data['is_anonymous'] ? Auth::user()->getMorphClass() : null,
                'notifiable_id' => ! $this->data['is_anonymous'] ? Auth::id() : null,
                'notification_type' => $this->data['notification_type'],
                'channel' => config('notifications.channels.' . $this->data['channel'] . '.driver'),
            ], [
                'is_active' => $this->data['is_active'],
            ]);

        if ($this->data['is_anonymous']) {
            $notificationSetting->channel_value = $this->data['channel_value'];
        }

        $notificationSetting->save();

        return $notificationSetting;
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
    public function handle(array $data, bool $isAnonymous = false): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: ! $isAnonymous ?
                new UpdateUserNotificationSettingsRequest() : new UpdateNotificationSettingsRequest()
        );

        foreach ($data as $item) {
            $notificationSetting = NotificationSetting::query()
                ->firstOrNew([
                    'notifiable_type' => ! $isAnonymous ? Auth::user()->getMorphClass() : null,
                    'notifiable_id' => ! $isAnonymous ? Auth::id() : null,
                    'notification_type' => $item['notification_type'],
                    'channel' => config('notifications.channels.' . $item['channel'] . '.driver'),
                ], [
                    'is_active' => $item['is_active'],
                ]);

            if ($isAnonymous) {
                $notificationSetting->channel_value = $item['channel_value'];
            }

            $notificationSetting->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $notificationSetting,
                additions: ['id' => $notificationSetting->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'notification settings updated',
            bulk: count($data) !== 0
        );
    }
}
