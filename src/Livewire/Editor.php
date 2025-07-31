<?php

namespace FluxErp\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use ReflectionClass;
use Throwable;

class Editor extends Component
{
    #[Modelable]
    public ?string $content = null;

    #[Locked]
    public array $bladeVariables = [];

    public ?string $edit = null;

    public ?string $label = null;

    public ?string $renderedPreview = null;

    public function mount(): void
    {
        $this->updateBladePreview();
    }

    public function updatedContent(): void
    {
        $this->updateBladePreview();
    }

    #[Renderless]
    public function updateBladePreview(): void
    {
        $data = array_map(
            fn(string $classOrMorph) => str_contains($classOrMorph, ':')
                ? morph_to($classOrMorph)
                : app($classOrMorph),
            $this->bladeVariables
        );

        try {
            $this->renderedPreview = Blade::render(
                html_entity_decode($this->content),
                $data
            );
        } catch (Throwable $exception) {
            $this->renderedPreview = '<div class="text-red-500">Error rendering Blade content: ' . $exception->getMessage() . '</div>';
        }
    }


    public function render(): View
    {
        return view('flux::livewire.editor');
    }
}
