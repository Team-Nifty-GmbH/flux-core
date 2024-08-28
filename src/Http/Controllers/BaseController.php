<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ModelFilter;
use FluxErp\Helpers\QueryBuilder;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Traits\Filterable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Scout\Searchable;

class BaseController extends Controller
{
    protected object $model;

    /**
     * @throws ValidationException
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $validation = [
            'include' => 'string',
        ];

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(statusCode: 404, data: $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        $includes = [];
        if ($validated['include'] ?? false) {
            $includes = explode(',', trim($validated['include'], " \t\n\r\0\x0B,"));
            $allowedIncludes = array_diff(array_keys($this->model->relationships()), ['additionalColumns']);
            $notAllowedIncludes = array_diff($includes, $allowedIncludes);

            if (count($notAllowedIncludes) > 0) {
                return ResponseHelper::createResponseFromBase(
                    statusCode: 422,
                    data: [
                        'include' => 'including \'' . implode(',', $notAllowedIncludes) . '\' not allowed',
                        'allowed_includes' => array_values($allowedIncludes),
                    ]
                );
            }
        }

        $instance = $this->model::query()
            ->whereKey($id)
            ->when($validated['include'] ?? false, function ($query) use ($includes) {
                return $query->with($includes);
            })
            ->first();

        if (empty($instance)) {
            return ResponseHelper::createResponseFromBase(statusCode: 404, data: ['id' => 'record not found']);
        }

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $instance)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->filled('search') && ! in_array(Searchable::class, class_uses($this->model))) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 400,
                data: ['search' => 'Search not allowed on given model.']
            );
        }

        $page = max((int) $request->page, 1);
        $perPage = $request->per_page > 500 || $request->per_page < 1 ? 25 : $request->per_page;

        if ($request->search) {
            $result = ModelFilter::filterModel(
                model: $this->model::class,
                search: $request->search,
                filter: $request->filter,
                include: $request->include,
                sort: $request->sort
            );

            $data = $result['data']->paginate($perPage, $page, [], $result['urlParams']);
        } else {
            $query = in_array(Filterable::class, class_uses_recursive($this->model)) ?
                QueryBuilder::filterModel($this->model, $request) :
                $this->model::query();
            $data = $query->paginate($perPage)->appends($request->query());
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $data,
            additions: $request->search ? ['url_params' => $result['urlParams']] : null
        )->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
