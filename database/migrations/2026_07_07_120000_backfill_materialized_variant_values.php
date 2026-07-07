<?php

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        (new MigrateProductVariantInheritanceJob())->handle();
    }

    // Data backfill isn't cleanly reversible (parent values already overwrote the stale
    // child columns/relations) — intentionally a no-op.
    public function down(): void {}
};
