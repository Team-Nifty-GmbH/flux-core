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

    public function initializeUsers(): void
    {
        $path = resource_path() . '/init-files/users.json';
        $json = json_decode(file_get_contents($path));

        if ($json->model === 'User') {
            $jsonUsers = $json->data;

            if ($jsonUsers) {
                foreach ($jsonUsers as $jsonUser) {
                    // Gather necessary foreign keys.
                    $languageId = Language::query()
                        ->where('language_code', $jsonUser->language_code)
                        ->first()
                        ?->id;

                    // Save to database, if all foreign keys are found.
                    if ($languageId && $jsonUser->user_code !== 'admin') {
                        User::query()
                            ->updateOrCreate([
                                'user_code' => $jsonUser->user_code,
                            ], [
                                'language_id' => $languageId,
                                'email' => $jsonUser->email,
                                'firstname' => $jsonUser->firstname,
                                'lastname' => $jsonUser->lastname,
                                'password' => $jsonUser->password,
                                'is_active' => $jsonUser->is_active,
                            ]);
                    }
                }
            }
        }
    }
}
