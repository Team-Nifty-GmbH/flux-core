<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->string('slug_position')->after('sort_number')->nullable();
        });

        $this->updateSort(false);
        $this->setSlugPosition();
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropColumn('slug_position');
        });

        $this->updateSort(true);
    }

    private function setSlugPosition(): void
    {
        // First, set slug_position for root-level records.
        DB::table('order_positions')
            ->whereNull('parent_id')
            ->update([
                'slug_position' => DB::raw("LPAD(sort_number, 8, '0')"),
            ]);

        // Then, iteratively update each level based on their parent positions.
        $updated = true;
        while ($updated) {
            // Perform an update query and check how many rows were affected.
            $updated = DB::table('order_positions as child')
                ->join('order_positions as parent', 'child.parent_id', '=', 'parent.id')
                ->whereNotNull('parent.slug_position')
                ->whereNull('child.slug_position')
                ->update([
                    'child.slug_position' => DB::raw(
                        "CONCAT(parent.slug_position, '.', LPAD(child.sort_number, 8, '0'))"
                    ),
                ]);
        }
    }

    private function updateSort(bool $rollback): void
    {
        DB::table('order_positions as child')
            ->joinSub(
                DB::table('order_positions')
                    ->select('id', 'order_id', 'parent_id')
                    ->addSelect(
                        DB::raw('ROW_NUMBER() OVER(PARTITION BY order_id' .
                            ($rollback ? '' : ', parent_id')
                            . ' ORDER BY sort_number) AS new_sort_number')
                    ),
                'sorted_positions',
                'child.id',
                '=',
                'sorted_positions.id'
            )
            ->update([
                'child.sort_number' => DB::raw('sorted_positions.new_sort_number'),
            ]);
    }
};
