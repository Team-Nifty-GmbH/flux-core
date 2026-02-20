<?php

use FluxErp\States\Ticket\TicketState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->timestamp('resolved_at')
                ->nullable()
                ->after('total_cost');
        });

        $endStates = resolve_static(TicketState::class, 'all')
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();

        DB::table('tickets')
            ->whereIn('state', $endStates)
            ->whereNull('resolved_at')
            ->update(['resolved_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropColumn('resolved_at');
        });
    }
};
