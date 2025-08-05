<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoggingTest extends TestCase
{
    public function test_create_database_log_entry(): void
    {
        $uuid = Str::uuid();
        $message = 'Alert message';
        Log::channel('database')->alert($message, ['uuid' => $uuid, 'text', 'key' => 'value']);

        $dbLog = DB::table('logs')
            ->where('foreign_uuid', $uuid)
            ->get();

        $this->assertEquals(1, $dbLog->count());
        $this->assertEquals($message, $dbLog[0]->message);
    }
}
