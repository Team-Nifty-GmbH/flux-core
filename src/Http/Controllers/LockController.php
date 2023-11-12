<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Lock\ForceUnlock;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\LockRecordRequest;
use Illuminate\Http\JsonResponse;

class LockController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showUserLocks(): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(statusCode: 200, data: auth()->user()->locks()->get());
    }

    public function lock(LockRecordRequest $request): JsonResponse
    {
        $model = $request->get('model_type')::query()
            ->whereKey($request->get('model_id'))
            ->firstOrFail();

        return $model->lock() || auth()->user()->is($model->getLockedBy())
            ? ResponseHelper::createResponseFromBase(
                statusCode: 201,
                data: $model,
                statusMessage: 'record locked'
            )
            : ResponseHelper::locked(
                'record is already locked by ' . $model->getLockedBy()?->name ?? 'unknown'
            );
    }

    public function unlock(LockRecordRequest $request): JsonResponse
    {
        $model = $request->get('model_type')::query()
            ->whereKey($request->get('model_id'))
            ->firstOrFail();

        $unlocked = ! $model->getHasLockAttribute() || $model->unlock();

        return $unlocked
            ? ResponseHelper::noContent()
            : ResponseHelper::locked('could not unlock record');
    }

    public function forceUnlock(LockRecordRequest $request)
    {
        $forceUnlock = ForceUnlock::make($request->all())->execute();

        return $forceUnlock
            ? ResponseHelper::noContent()
            : ResponseHelper::locked('could not unlock record');
    }
}
