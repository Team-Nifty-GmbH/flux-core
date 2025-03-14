<?php

namespace FluxErp\Logging;

use Carbon\Carbon;
use FluxErp\Models\Log;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Throwable;

class DatabaseLoggingHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $context = (object) $record['context'];
        $uuid = property_exists($context, 'uuid') && ! empty($context->uuid) ? $context->uuid : null;
        unset($context->uuid);

        $data = [
            'foreign_uuid' => $uuid,
            'message' => data_get($record, 'message'),
            'context' => json_encode($context),
            'level' => data_get($record, 'level'),
            'level_name' => data_get($record, 'level_name'),
            'channel' => data_get($record, 'channel'),
            'record_datetime' => Carbon::parse(data_get($record, 'datetime'))->toDateTimeString(),
            'extra' => json_encode(data_get($record, 'extra', [])),
            'formatted' => data_get($record, 'formatted'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $id = DB::table('logs')->insertGetId($data);

            if (config('broadcasting.default') !== 'log') {
                resolve_static(Log::class, 'query')
                    ->whereKey($id)
                    ->first()
                    ->newBroadcastableModelEvent('created');
            }
        } catch (Throwable) {
            // fallback to single logging of laravel
        }
    }
}
