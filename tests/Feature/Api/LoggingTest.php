<?php

uses(FluxErp\Tests\TestCase::class);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

test('create database log entry', function (): void {
    $uuid = Str::uuid();
    $message = 'Alert message';
    Log::channel('database')->alert($message, ['uuid' => $uuid, 'text', 'key' => 'value']);

    $dbLog = DB::table('logs')
        ->where('foreign_uuid', $uuid)
        ->get();

    expect($dbLog->count())->toEqual(1);
    expect($dbLog[0]->message)->toEqual($message);
});
