<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Lock\CreateLock;
use FluxErp\Actions\Lock\DeleteLock;
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
            ->first();

        return CreateLock::make($request->all())->execute()
            ? ResponseHelper::createResponseFromBase(
                statusCode: 200,
                data: $model,
                statusMessage: 'record locked'
            )
            : ResponseHelper::locked(
                'record is already locked by ' . $model->getLockedBy()?->name ?? 'unknown'
            );
    }

    public function unlock(LockRecordRequest $request): JsonResponse
    {
        $unlocked = DeleteLock::make($request->all())->execute();

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
