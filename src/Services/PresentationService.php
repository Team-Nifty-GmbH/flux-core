<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdatePresentationRequest;
use FluxErp\Models\Presentation;
use Illuminate\Contracts\View\View;

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
        $presentation = new Presentation();

        if (($data['model_type'] ?? false) && ($data['model_id'] ?? false)) {
            $model = $data['model_type'];

            if (! class_exists($model)) {
                $model = Helper::classExists(classString: ucfirst($data['model_type']), isModel: true);
                if (! $model) {
                    return ResponseHelper::createArrayResponse(
                        statusCode: 404,
                        data: ['model_type' => 'model type not found']
                    );
                }
            }

            $modelInstance = $model::query()->whereKey($data['model_id'])->first();

            if (empty($modelInstance)) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_id' => 'model instance not found']
                );
            }
        }

        $presentation->fill($data);
        $presentation->save();

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

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdatePresentationRequest(),
            service: $this,
            model: new Presentation()
        );

        foreach ($data as $item) {
            $presentation = Presentation::query()
                ->whereKey($item['id'])
                ->first();

            $presentation->fill($item);
            $presentation->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $presentation->withoutRelations()->fresh(),
                additions: ['id' => $presentation->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'presentations updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $presentation = Presentation::query()
            ->whereKey($id)
            ->first();

        if (! $presentation) {
            return ResponseHelper::createArrayResponse(statusCode: 404, data: ['id' => 'presentation not found']);
        }

        $presentation->delete();

        return ResponseHelper::createArrayResponse(statusCode: 204, statusMessage: 'presentation deleted');
    }

    public function validateItem(array $item, array $response): ?array
    {
        if (($item['model_type'] ?? false) && ($item['model_id'] ?? false)) {
            $model = $item['model_type'];

            if (! class_exists($model)) {
                $model = Helper::classExists(classString: $item['model_type'], isModel: true);

                if (! $model) {
                    return ResponseHelper::createArrayResponse(
                        statusCode: 404,
                        data: ['model_type', 'model type not found'],
                        additions: $response
                    );
                }
            }

            $modelInstance = $model::query()->whereKey($item['model_id'])->first();

            if (empty($modelInstance)) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_id' => 'model instance not found'],
                    additions: $response
                );
            }
        }

        return null;
    }
}
