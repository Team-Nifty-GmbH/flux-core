<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\LockRequest;
use FluxErp\Models\Lock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LockController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->per_page > 500 || $request->per_page < 1 ? 25 : $request->per_page;

        $locks = resolve_static(Lock::class, 'query')
            ->paginate($perPage);

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $locks);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function lock(string $modelType, Request $request): JsonResponse
    {
        $modelClass = Helper::classExists($modelType, isModel: true);

        if (! $modelClass) {
            return ResponseHelper::createResponseFromBase(statusCode: 404);
        }

        $columns = $modelClass::getColumns()
            ->filter(function ($item) {
                return str_contains($item->Type, 'int');
            })
            ->pluck('Field')
            ->toArray();

        $validationStrings = array_fill(0, count($columns), 'sometimes|required|integer');

        $validator = Validator::make(
            $request->all(),
            array_merge(array_combine($columns, $validationStrings), (new LockRequest())->rules())
        );

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(statusCode: 422);
        }

        $validated = $validator->validated();

        $lock = $validated['lock'] ?? true;
        unset($validated['lock']);

        $query = $modelClass::query()
            ->when($lock, function (Builder $query) {
                return $query->whereDoesntHave('lock');
            }, function (Builder $query) use ($request) {
                return $query->whereRelation('lock', 'created_by', $request->user()->id);
            });

        foreach ($validated as $key => $value) {
            $query->where($key, $value);
        }

        $instances = $query->get();

        if (! $instances) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 202,
                statusMessage: 'no records found to (un)lock'
            );
        }

        if ($lock) {
            foreach ($instances as $instance) {
                $instance->lock()->create();
            }
        } else {
            resolve_static(Lock::class, 'query')
                ->where('model_type', $modelClass)
                ->whereIntegerInRaw('model_id', $instances->pluck('id')->toArray())
                ->where('created_by', $request->user()->id)
                ->delete();
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $instances->pluck('id'),
            statusMessage: $lock ? 'records locked' : 'records unlocked'
        );
    }

    public function showUserLocks(Request $request): JsonResponse
    {
        $locks = resolve_static(Lock::class, 'query')
            ->where('created_by', Auth::id())
            ->get();

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $locks);
    }
}
