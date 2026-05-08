<?php

namespace FluxErp\Console\Commands;

use FluxErp\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductVariantsAuditCommand extends Command
{
    protected $description = 'Audit existing parent products for references that block parent-not-orderable enforcement.';

    protected $signature = 'flux:product-variants:audit {--output=table : Output format: table or csv}';

    public function handle(): int
    {
        $rows = $this->collectReferences();

        if ($rows->isEmpty()) {
            $this->info('No issues found. All parent products are clean.');

            return self::SUCCESS;
        }

        if ($this->option('output') === 'csv') {
            $path = $this->writeCsv($rows);
            $this->info("CSV written to: {$path}");
        } else {
            $this->table(
                ['parent_id', 'product_number', 'reference_type', 'reference_count'],
                $rows->toArray()
            );
        }

        return self::SUCCESS;
    }

    protected function collectReferences(): Collection
    {
        $rows = collect();

        $parents = resolve_static(Product::class, 'query')
            ->where(fn (Builder $query) => $query
                ->whereHas('children')
                ->orWhere('was_parent', true)
            )
            ->get(['id', 'product_number', 'is_active_export_to_web_shop']);

        foreach ($parents as $parent) {
            $orderPositions = DB::table('order_positions')
                ->whereNull('deleted_at')
                ->where('product_id', $parent->getKey())
                ->count();

            if ($orderPositions > 0) {
                $rows->push([
                    'parent_id' => $parent->getKey(),
                    'product_number' => $parent->product_number,
                    'reference_type' => 'order_positions',
                    'reference_count' => $orderPositions,
                ]);
            }

            $stockPostings = DB::table('stock_postings')
                ->where('product_id', $parent->getKey())
                ->count();

            if ($stockPostings > 0) {
                $rows->push([
                    'parent_id' => $parent->getKey(),
                    'product_number' => $parent->product_number,
                    'reference_type' => 'stock_postings',
                    'reference_count' => $stockPostings,
                ]);
            }

            $bundleRefs = DB::table('bundle_product_product')
                ->where('product_id', $parent->getKey())
                ->count();

            if ($bundleRefs > 0) {
                $rows->push([
                    'parent_id' => $parent->getKey(),
                    'product_number' => $parent->product_number,
                    'reference_type' => 'bundle_components',
                    'reference_count' => $bundleRefs,
                ]);
            }

            if ($parent->is_active_export_to_web_shop) {
                $rows->push([
                    'parent_id' => $parent->getKey(),
                    'product_number' => $parent->product_number,
                    'reference_type' => 'web_shop_active',
                    'reference_count' => 1,
                ]);
            }
        }

        return $rows;
    }

    protected function writeCsv(Collection $rows): string
    {
        $path = storage_path('app/product-variants-audit-' . now()->format('Ymd-His-u') . '.csv');
        $handle = fopen($path, 'w');
        fputcsv($handle, ['parent_id', 'product_number', 'reference_type', 'reference_count']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['parent_id'],
                $row['product_number'],
                $row['reference_type'],
                $row['reference_count'],
            ]);
        }

        fclose($handle);

        return $path;
    }
}
