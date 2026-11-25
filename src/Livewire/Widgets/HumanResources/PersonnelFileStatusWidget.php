<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class PersonnelFileStatusWidget extends Component
{
    use Widgetable;

    #[Locked]
    public array $expiredDocuments = [];

    #[Locked]
    public array $missingDocuments = [];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 5;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.personnel-file-status');
    }

    public function loadData(): void
    {
        $this->missingDocuments = $this->missingDocuments()->all();
        $this->expiredDocuments = $this->expiredDocuments()->all();
    }

    protected function expiredDocuments(): Collection
    {
        $counts = resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->where('model_type', morph_alias(Employee::class))
            ->whereNotNull('document_type_id')
            ->where('retain_until', '<', today())
            ->groupBy('model_id')
            ->selectRaw('model_id, COUNT(*) AS count')
            ->pluck('count', 'model_id');

        return resolve_static(Employee::class, 'query')
            ->whereIntegerInRaw('id', $counts->keys()->all())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Employee $employee): array => [
                'id' => $employee->getKey(),
                'name' => $employee->name,
                'count' => (int) $counts->get($employee->getKey()),
            ]);
    }

    protected function missingDocuments(): Collection
    {
        $documentTypes = resolve_static(DocumentType::class, 'query')
            ->where('is_required', true)
            ->where('is_active', true)
            ->get(['id', 'name', 'model_types'])
            ->filter(fn (DocumentType $documentType): bool => $documentType->isAllowedFor(
                morph_alias(Employee::class)
            ));

        if ($documentTypes->isEmpty()) {
            return collect();
        }

        $employees = resolve_static(Employee::class, 'query')
            ->employed(today())
            ->orderBy('name')
            ->get(['id', 'name']);

        $approved = resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->where('model_type', morph_alias(Employee::class))
            ->whereIntegerInRaw('model_id', $employees->modelKeys())
            ->whereIntegerInRaw('document_type_id', $documentTypes->modelKeys())
            ->where('review_state', DocumentReviewStateEnum::Approved)
            ->get(['model_id', 'document_type_id'])
            ->groupBy('model_id')
            ->map(fn (Collection $media): array => $media->pluck('document_type_id')->all());

        return $employees
            ->map(fn (Employee $employee): array => [
                'id' => $employee->getKey(),
                'name' => $employee->name,
                'document_types' => $documentTypes
                    ->reject(fn (DocumentType $documentType): bool => in_array(
                        $documentType->getKey(),
                        $approved->get($employee->getKey(), []),
                        true
                    ))
                    ->pluck('name')
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $employee): bool => (bool) data_get($employee, 'document_types'))
            ->values();
    }

    protected function liveUpdates(): array
    {
        return [
            $this->modelChannel(Employee::class) => [
                Employee::class => ['created', 'updated', 'deleted'],
            ],
        ];
    }
}
