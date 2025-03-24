<?php

namespace FluxErp\Traits\Livewire\DataTable;

use FluxErp\Models\Language;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;

trait SupportsLocalization
{
    public ?int $languageId;

    public function localize(): void
    {
        Session::put('selectedLanguageId', $this->languageId);

        $this->loadData();
    }

    public function mountSupportsLocalization(): void
    {
        $this->languageId = Session::get('selectedLanguageId')
            ?? resolve_static(Language::class, 'default')?->getKey();
    }

    protected function getTableActionsSupportsLocalization(): array
    {
        $languages = resolve_static(Language::class, 'query')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        return [
            new HtmlString(
                Blade::render(
                    '<x-select.styled
                        required
                        x-model="$wire.languageId"
                        x-on:select="$wire.localize()"
                        select="label:name|value:id"
                        :options="$languages"
                    />',
                    ['languages' => $languages]
                )
            ),
        ];
    }
}
