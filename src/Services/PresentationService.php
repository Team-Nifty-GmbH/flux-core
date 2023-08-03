<?php

namespace FluxErp\Services;

use FluxErp\Actions\Presentation\CreatePresentation;
use FluxErp\Actions\Presentation\DeletePresentation;
use FluxErp\Actions\Presentation\UpdatePresentation;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Presentation;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class PresentationService
{
    public function showHtml(string $id, bool $asPdf = false): array|View
    {
        $presentation = Presentation::query()
            ->whereKey($id)
            ->first();

        if (! $presentation) {
            return ResponseHelper::createArrayResponse(statusCode: 404, data: ['id' => 'presentation not found']);
        }

        foreach ($presentation->printData as $printData) {
            if (! view()->exists($printData->view)) {
                return ResponseHelper::createArrayResponse(statusCode: 404, data: ['view' => 'view not found']);
            }
        }

        if ($asPdf) {
            $printService = new PrintDataService([['name' => 'preferCssPageSize', 'contents' => true]]);
            $content = $printService->getHtmlContent($presentation->printData, true);

            return $printService->generatePdfFromHtml($content);
        } else {
            $printService = new PrintDataService();

            return $printService->getHtmlContent($presentation->printData, true);
        }
    }

    public function create(array $data): array
    {
        try {
            $presentation = CreatePresentation::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $presentation->refresh(),
            statusMessage: 'presentation created'
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
                    data: $presentation = UpdatePresentation::make($item)->validate()->execute(),
                    additions: ['id' => $presentation->id]
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
            statusMessage: $statusCode === 422 ? null : 'presentation(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeletePresentation::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'presentation deleted'
        );
    }
}
