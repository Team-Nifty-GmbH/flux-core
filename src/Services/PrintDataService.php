<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\GeneratePdfFromViewNoTemplateIdsRequest;
use FluxErp\Models\PrintData;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\MultipartStream;
use Http\Discovery\Psr17FactoryDiscovery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class PrintDataService
{
    private array $multipartFormData;

    /**
     * Get print layouts and templates for a given path
     */
    public function getPrintViews(string $path = null): array
    {
        $basePath = resource_path('views/print/' . $path);

        try {
            $files = File::allFiles($basePath);
        } catch (DirectoryNotFoundException $e) {
            return ResponseHelper::createArrayResponse(statusCode: 404, statusMessage: $e->getMessage());
        }

        $response = [];
        $responsePath = $path ? '.' . $path . '.' : '.';
        foreach ($files as $file) {
            $relativePathname = $file->getRelativePathname();
            if (str_ends_with($relativePathname, '.blade.php')) {
                $thumb = $this->getViewThumbnail($basePath . '/' . $relativePathname);

                $layout = 'print' . $responsePath . str_replace(['.blade.php', '/'], ['', '.'], $relativePathname);

                $response[] = [
                    'name' => str_replace(
                        '.blade.php',
                        '',
                        str_replace('_custom', '', $file->getFilename())
                    ),
                    'thumb' => $thumb,
                    'view' => $layout,
                    'templates' => PrintData::query()
                        ->select(['id', 'template_name'])
                        ->where('view', $layout)
                        ->where('is_template', true)
                        ->get()
                        ->makeHidden('data'),
                ];
            }
        }

        return ResponseHelper::createArrayResponse(statusCode: 200, data: $response);
    }

    /**
     * Returns the view with the payload data as html
     */
    public function showHtml(array $data, string $id, bool $asPdf = false): array|View
    {
        $printData = PrintData::query()
            ->whereKey($id)
            ->first();

        if (! $printData) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'print data not found']
            );
        }

        if (! view()->exists($printData->view)) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['view' => 'view not found']
            );
        }

        if ($asPdf) {
            $this->addFormValuesToMultiPartFormData($data);

            $content = $this->showHtml($data, $id);

            return $this->generatePdfFromHtml($content);
        } else {
            return $this->getHtmlContent(
                $printData,
                $data['noLayout'] ?? false
            );
        }
    }

    public function generatePdfFromView(array $data): array|View
    {
        $modelClass = null;
        if (array_key_exists('model_id', $data) && array_key_exists('model_type', $data)) {
            $modelClass = Helper::classExists(classString: ucfirst($data['model_type']), isModel: true);
            if (! $modelClass) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_type' => 'model type not found']
                );
            }

            $modelInstance = $modelClass::query()->whereKey($data['model_id'])->first();
            if (empty($modelInstance)) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_id' => 'model instance not found']
                );
            }
        }

        if (($data['template_ids'] ?? false) && count($data['template_ids']) > 0) {
            $validator = Validator::make($data, [
                (new GeneratePdfFromViewNoTemplateIdsRequest())->rules(),
            ]);

            if ($validator->fails()) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $validator->errors()->toArray()
                );
            }

            $templateIds = array_filter($data['template_ids'], function ($value) {
                return is_int($value) && $value > 0;
            });

            $responses = [];
            $errors = [];
            $mediaService = new MediaService();
            foreach ($templateIds as $templateId) {
                $printData = PrintData::query()
                    ->whereKey($templateId)
                    ->first();

                if (! $printData) {
                    $responses[] = ResponseHelper::createArrayResponse(
                        statusCode: 404,
                        data: ['id' => 'record not found']
                    );

                    $errors[] = true;

                    continue;
                }

                $data['view'] = $printData->view;
                $data['data'] = $printData->data;
                $data['template_name'] = null;
                $data['model_type'] = $modelClass;
                $responses[] = $this->storePrintData($data);

                if ($printData->thumbnail?->data) {
                    $thumbnailData = [
                        'model_id' => end($responses)['data']['id'],
                        'model_type' => 'PrintData',
                        'media' => $printData->thumbnail->data,
                        'is_thumb' => true,
                    ];

                    $mediaService->upload($thumbnailData);
                }
            }

            $statusCode = count($responses) === count($errors) ? 404 : (count($errors) < 1 ? 200 : 207);

            return ResponseHelper::createArrayResponse(
                statusCode: $statusCode,
                data: $responses,
                bulk: true
            );
        } elseif ($data['preview'] ?? false) {
            return $this->getHtmlContent(new PrintData([
                'model_type' => $modelClass,
                'model_id' => $modelClass ? $modelInstance->id : null,
                'view' => $data['view'],
                'data' => $data['data'],
            ]));
        }

        $this->addFormValuesToMultiPartFormData($data);

        $content = $this->getHtmlContent(new PrintData([
            'model_type' => $modelClass,
            'model_id' => $modelClass ? $modelInstance->id : null,
            'view' => $data['view'],
            'data' => $data['data'],
        ]));

        if ($data['preview'] ?? false) {
            return $content;
        }

        $responses = [];

        // Store the request and return the stored record
        if ($data['store'] ?? false) {
            $printData = new PrintData();
            $printData->fill([
                'data' => $data['data'],
                'view' => $data['view'],
                'is_public' => $data['is_public'] ?? false,
                'is_template' => $data['is_template'] ?? false,
                'template_name' => $data['template_name'] ?? null,
                'sort' => $data['sort'] ?? null,
                'model_id' => $data['model_id'] ?? null,
                'model_type' => $modelClass,
            ]);
            $this->setSort($printData)->save();

            $content = $this->getHtmlContent($printData);

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 201,
                data: $printData->makeHidden('data')->toArray(),
                additions: ['url' => route('print.show-html', ['id' => $printData->id])]
            );
        }

        if (! ($data['store'] ?? false) || ($data['store_pdf'] ?? false)) {
            $stream = $this->getStream($content);
            $this->addFileStreamToMultipartFormData($stream);

            $client = new Client();
            try {
                $pdf = $client->sendRequest($this->generateRequest());
                $pdfContents = $pdf->getBody()->getContents();

                // store the pdf
                if (($data['store_pdf'] ?? false) && ($data['store'] ?? false)) {
                    $temp = tmpfile();
                    fwrite($temp, $pdf->getBody()->getContents());
                    $file = new UploadedFile(
                        stream_get_meta_data($temp)['uri'],
                        Str::uuid() . '.pdf',
                        'application/pdf'
                    );
                    fclose($temp);

                    $mediaService = new MediaService();
                    $media = $mediaService->upload([
                        'model_id' => $printData->id,
                        'model_type' => 'PrintData',
                        'media' => $file,
                        'is_public' => $data['store_pdf_public'] ?? false,
                    ]);

                    $responses[] = $media;
                }
            } catch (ClientExceptionInterface $e) {
                Log::error($e->getMessage());
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: ['pdf' => 'pdf generation failed']
                );
            }
        }

        if (count($responses) > 1) {
            $responses = collect($responses);

            return ResponseHelper::createArrayResponse(
                statusCode: count($responses->where('status', '>', 299)->all()) > 0 ? 207 : 201,
                data: $responses,
                bulk: true
            );
        } elseif (count($responses) === 1) {
            return $responses[0];
        } else {
            return ResponseHelper::createArrayResponse(
                statusCode: 200,
                additions: ['pdf' => $pdfContents]
            );
        }
    }

    public function update(array $data): array
    {
        $modelClass = null;
        if (($data['model_id'] ?? false) && ($data['model_type'] ?? false)) {
            $modelClass = Helper::classExists(classString: ucfirst($data['model_type']), isModel: true);
            if (! $modelClass) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_type' => 'model type not found']
                );
            }

            $modelInstance = $modelClass::query()->whereKey($data['model_id'])->first();
            if (empty($modelInstance)) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_id' => 'model instance not found']
                );
            }
        }

        $printData = PrintData::query()
            ->whereKey($data['id'])
            ->first();

        $sort = array_key_exists('sort', $data) && $data['sort'] !== $printData->sort;
        $prevSort = $printData->sort;

        $printData->fill([
            'data' => $data['data'] ?? $printData->data,
            'view' => $data['view'] ?? $printData->view,
            'is_public' => $data['is_public'] ?? $printData->is_public,
            'is_template' => $data['is_template'] ?? $printData->is_template,
            'template_name' => $data['template_name'] ?? $printData->template_name,
            'sort' => $data['sort'] ?? $printData->sort,
            'model_id' => $data['model_id'] ?? $printData->model_id,
            'model_type' => array_key_exists('model_type', $data) ? $modelClass : $printData->model_type,
        ]);
        $this->setSort($printData, $sort, $prevSort)->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $printData->makeHidden('data')->toArray(),
            additions: ['url' => route('print.show-html', ['id' => $printData->id])]
        );
    }

    public function delete(string $id): array
    {
        $printData = PrintData::query()
            ->whereKey($id)
            ->first();

        if (empty($printData)) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'print data not found']
            );
        }

        $printData->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'print data deleted'
        );
    }

    public function getHtmlContent(Collection|Model $printData, bool $noLayout = false): View
    {
        $data = $printData;

        if (! is_a($printData, Collection::class)) {
            if ($noLayout) {
                $bladeData = (array) $printData->data;
                $bladeData['model'] = $printData;

                return view($printData->view, $bladeData);
            }

            $data = new Collection();
            $data->add($printData);
        }

        return view('flux::print.print', ['printData' => $data, 'noLayout' => $noLayout]);
    }

    public function generatePdfFromHtml($content): array
    {
        $stream = $this->getStream($content);
        $this->addFileStreamToMultipartFormData($stream);
        $client = new Client();

        try {
            $pdf = $client->sendRequest($this->generateRequest());
            $pdfContents = $pdf->getBody()->getContents();
        } catch (ClientExceptionInterface $e) {
            Log::error($e->getMessage());

            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: ['pdf' => 'pdf generation failed']
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            additions: ['pdf' => $pdfContents]
        );
    }

    public function getViewThumbnail($path): string
    {
        $thumbPath = str_replace('.blade.php', '', $path) . '.*[!{php}]';
        $files = File::glob($thumbPath, GLOB_BRACE);

        $image = false;
        $mimeType = false;
        foreach ($files as $file) {
            $mimeType = File::mimeType($file);
            if (explode('/', $mimeType)[0] == 'image') {
                $image = File::get($file);
                break;
            }
        }

        return $image && $mimeType ?
            'data:' . $mimeType . ';base64,' . base64_encode($image) :
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPj/HwADBwIAMCbHYQAAAABJRU5ErkJggg==';
    }

    private function generateRequest(): RequestInterface
    {
        $body = new MultipartStream($this->multipartFormData);

        $url = config('flux.gotenberg.host') . ':' . config('flux.gotenberg.port') . '/';
        $endpoint = 'forms/chromium/convert/html';

        return Psr17FactoryDiscovery::findRequestFactory()
            ->createRequest('POST', $url . $endpoint)
            ->withHeader('Content-Type', 'multipart/form-data; boundary="' . $body->getBoundary() . '"')
            ->withBody($body);
    }

    /**
     * @return false|resource
     */
    private function getStream(string $streamContent)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $streamContent);
        rewind($stream);

        return $stream;
    }

    private function addFormValuesToMultiPartFormData(array $data): void
    {
        $this->addFormValueToMultipartFormData('marginTop', $data['margin_top'] ?? '0');
        $this->addFormValueToMultipartFormData('marginBottom', $data['margin_bottom'] ?? '0');
        $this->addFormValueToMultipartFormData('marginLeft', $data['margin_left'] ?? '0');
        $this->addFormValueToMultipartFormData('marginRight', $data['margin_right'] ?? '0');
        $this->addFormValueToMultipartFormData('paperWidth', $data['paper_width'] ?? '8.27');
        $this->addFormValueToMultipartFormData('paperHeight', $data['paper_height'] ?? '11.7');
        $this->addFormValueToMultipartFormData('preferCssPageSize', $data['prefer_css_page_size'] ?? true);
    }

    private function addFormValueToMultipartFormData(string $name, string $value): void
    {
        $this->multipartFormData[] = [
            'name' => $name,
            'contents' => $value,
        ];
    }

    private function addFileStreamToMultipartFormData(mixed $stream): void
    {
        $this->multipartFormData[] = [
            'name' => 'files',
            'filename' => 'index.html',
            'contents' => $stream,
        ];
    }

    private function storePrintData(array $data): array
    {
        $printData = new PrintData();
        $printData->fill([
            'data' => $data['data'],
            'view' => $data['view'],
            'is_public' => $data['is_public'] ?? false,
            'is_template' => $data['is_template'] ?? false,
            'template_name' => $data['template_name'] ?? null,
            'sort' => $data['sort'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'model_type' => $data['model_type'],
        ]);
        $this->setSort($printData)->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $printData->makeHidden('data')->toArray(),
            additions: ['url' => route('print.show-html', ['id' => $printData->id])]
        );
    }

    private function setSort(Model $printData, bool $sort = true, int $prevSort = null): Model
    {
        if ($printData->model_type === null || $printData->model_id === null) {
            $printData->sort = 1;

            return $printData;
        }

        if (! $sort) {
            return $printData;
        }

        $collection = PrintData::query()
            ->where('model_type', $printData->model_type)
            ->where('model_id', $printData->model_id)
            ->where('id', '!=', $printData->id)
            ->orderBy('sort')
            ->get();

        if (count($collection) > 1 && $printData->sort !== null) {
            $collection->splice($printData->sort - 1, 0, [$printData]);
        } elseif (count($collection) === 1) {
            if ($collection[0]->sort > $prevSort) {
                $collection->push($printData);
            } else {
                $collection->prepend($printData);
            }
        } else {
            $collection->push($printData);
        }

        $collection->map(function ($item, $key) {
            $item->sort = (int) $key + 1;
        });

        foreach ($collection as $item) {
            $item->save();
        }

        return $collection->where('id', $printData->id)->first();
    }
}
