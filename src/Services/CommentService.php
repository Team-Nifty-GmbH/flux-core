<?php

namespace FluxErp\Services;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;

class CommentService
{
    public function create(array $data): array
    {
        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: CreateComment::make($data)->validate()->execute(),
            statusMessage: __('comment created')
        );
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $comment = UpdateComment::make($item)->validate()->execute(),
                    additions: ['id' => $comment->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : __('comment(s) updated'),
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteComment::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 403,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: __('comment deleted')
        );
    }
}
