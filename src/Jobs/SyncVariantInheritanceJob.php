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
use Illuminate\Support\Facades\DB;

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
        $fields = array_intersect($fields, Product::getTranslatableAttributes());

        if (! $fields) {
            return;
        }

        $modelType = $parent->getMorphClass();

        foreach ($fields as $field) {
            $childIds = resolve_static(Product::class, 'query')
                ->where('parent_id', $parent->getKey())
                ->where(fn ($q) => $q->whereNull('overridden_fields')
                    ->orWhereJsonDoesntContain('overridden_fields', $field))
                ->pluck('id');

            if ($childIds->isEmpty()) {
                continue;
            }

            $parentTranslations = DB::table('attribute_translations')
                ->where('model_type', $modelType)
                ->where('model_id', $parent->getKey())
                ->where('attribute', $field)
                ->whereNull('deleted_at')
                ->pluck('value', 'language_id');

            // Drop child rows for locales the parent no longer has (all of them, if none are left).
            DB::table('attribute_translations')
                ->where('model_type', $modelType)
                ->whereIn('model_id', $childIds)
                ->where('attribute', $field)
                ->whereNull('deleted_at')
                ->when(
                    $parentTranslations->isNotEmpty(),
                    fn ($query) => $query->whereNotIn('language_id', $parentTranslations->keys()),
                )
                ->update(['deleted_at' => now()]);

            foreach ($parentTranslations as $languageId => $value) {
                $existingChildIds = DB::table('attribute_translations')
                    ->where('model_type', $modelType)
                    ->whereIn('model_id', $childIds)
                    ->where('attribute', $field)
                    ->where('language_id', $languageId)
                    ->whereNull('deleted_at')
                    ->pluck('model_id');

                if ($existingChildIds->isNotEmpty()) {
                    DB::table('attribute_translations')
                        ->where('model_type', $modelType)
                        ->whereIn('model_id', $existingChildIds)
                        ->where('attribute', $field)
                        ->where('language_id', $languageId)
                        ->whereNull('deleted_at')
                        ->update(['value' => $value, 'updated_at' => now()]);
                }

                $missingChildIds = $childIds->diff($existingChildIds);

                if ($missingChildIds->isNotEmpty()) {
                    DB::table('attribute_translations')->insert(
                        $missingChildIds->map(fn ($childId) => [
                            'language_id' => $languageId,
                            'model_type' => $modelType,
                            'model_id' => $childId,
                            'attribute' => $field,
                            'value' => $value,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])->all()
                    );
                }
            }
        }
    }
}
