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
            $table->unsignedBigInteger('resolved_by')
                ->nullable()
                ->after('total_cost');
            $table->timestamp('resolved_at')
                ->nullable()
                ->after('resolved_by');
        });

        $endStates = resolve_static(TicketState::class, 'all')
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();

        DB::table('tickets')
            ->whereIn('state', $endStates)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => DB::raw('updated_at'),
                'resolved_by' => DB::raw('updated_by'),
            ]);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropColumn(['resolved_by', 'resolved_at']);
        });
    }
};
