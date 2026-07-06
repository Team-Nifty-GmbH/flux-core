<?php

namespace FluxErp\Jobs;

use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SyncVariantInheritanceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $fields
     */
    public function __construct(public int $parentId, public array $fields = []) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping('variant-sync:' . $this->parentId)];
    }

    public function handle(): void
    {
        if (! app(ProductSettings::class)->variant_inheritance_enabled) {
            return;
        }

        /** @var Product|null $parent */
        $parent = resolve_static(Product::class, 'query')->whereKey($this->parentId)->first();
        if (! $parent) {
            return;
        }

        $fields = $this->fields ?: $parent->getInheritableFields();

        foreach ($fields as $field) {
            resolve_static(Product::class, 'query')
                ->where('parent_id', $this->parentId)
                ->where(fn ($q) => $q->whereNull('overridden_fields')
                    ->orWhereJsonDoesntContain('overridden_fields', $field))
                ->update([$field => $parent->getRawOriginal($field)]);
        }

        $this->syncTranslations($parent, $fields);

        // Query-builder updates bypass Scout's ModelObserver — reindex explicitly.
        resolve_static(Product::class, 'query')
            ->where('parent_id', $this->parentId)
            ->searchable();
    }

    protected function syncTranslations(Product $parent, array $fields): void
    {
        // Implemented in Task 3.
    }
}
