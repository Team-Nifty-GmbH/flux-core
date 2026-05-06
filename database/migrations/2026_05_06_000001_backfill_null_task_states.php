<?php

use FluxErp\States\Task\Open;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // tasks.state is `string('state')->default('open')` and therefore
        // declared NOT NULL with default 'open'. Some customer databases
        // still carry rows with state IS NULL from before the state column
        // was introduced or from manual SQL fixes that bypassed the
        // default. Hydrating such a row into FluxErp\Livewire\Forms\TaskForm
        // raises "Cannot assign null to property of type string".
        //
        // Backfill those rows to the default state so the form (and every
        // other consumer that reasonably assumes a non-null state) works
        // again.
        DB::table('tasks')
            ->whereNull('state')
            ->update(['state' => Open::$name]);
    }

    public function down(): void
    {
        // Lossy: we cannot recover which rows previously held NULL.
    }
};
