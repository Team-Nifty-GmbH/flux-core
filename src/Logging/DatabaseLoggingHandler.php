<?php

namespace FluxErp\Logging;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class DatabaseLoggingHandler extends AbstractProcessingHandler
{
    private string $table;

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        $this->table = 'logs';
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $context = (object) $record['context'];
        $uuid = property_exists($context, 'uuid') && ! empty($context->uuid) ? $context->uuid : null;
        unset($context->uuid);

        $data = [
            'foreign_uuid' => $uuid,
            'message' => $record['message'],
            'context' => json_encode($context),
            'level' => $record['level'],
            'level_name' => $record['level_name'],
            'channel' => $record['channel'],
            'record_datetime' => Carbon::parse($record['datetime'])->toDateTimeString(),
            'extra' => json_encode($record['extra']),
            'formatted' => $record['formatted'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::table($this->table)->insert($data);
    }
}
