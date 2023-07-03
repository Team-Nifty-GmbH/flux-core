<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateUserRequest;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function create(array $data): User
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['language_id'] = array_key_exists('language_id', $data) ?
            $data['language_id'] :
            Language::query()->where('language_code', config('app.locale'))->first()?->id;

        $user = new User($data);
        $user->save();

        return $user->refresh();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateUserRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $user = User::query()
                ->whereKey($item['id'])
                ->first();

            $user->fill($item);
            $user->save();

            // Delete all tokens of the user if the user is set to is_active = false
            if (! ($item['is_active'] ?? true)) {
                $user->tokens()->delete();
            }

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $user->withoutRelations()->fresh(),
                additions: ['id' => $user->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'users updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $user = User::query()
            ->whereKey($id)
            ->first();

        if (! $user) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => __('user not found')]
            );
        }

        if ($user->id === Auth::id()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: ['id' => __('cant delete yourself')]
            );
        }

        $user->tokens()->delete();
        $user->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'user deleted'
        );
    }
}
