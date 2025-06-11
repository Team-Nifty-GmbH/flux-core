<?php

namespace FluxErp\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Editor extends Component
{
    public function __construct(
        public ?string $id = null,
        public bool $bold = true,
        public bool $italic = true,
        public bool $underline = true,
        public bool $strike = true,
        public bool $code = true,
        public bool $h1 = true,
        public bool $h2 = true,
        public bool $h3 = true,
        public bool $horizontalRule = true,
        public bool $bulletList = true,
        public bool $orderedList = true,
        public bool $quote = true,
        public bool $codeBlock = true,

        public bool $tooltipDropdown = false,
        public bool $transparent = false,
        public ?int $defaultFontSize = null,
        public array $availableFontSizes = [
            12,
            14,
            16,
            18,
            20,
            24,
            28,
            32,
            36,
        ],
        public array $colorPalette = [
            'red' => [
                '#FFEBEE',
                '#FFCDD2',
                '#EF9A9A',
                '#E57373',
                '#EF5350',
                '#F44336',
                '#E53935',
                '#D32F2F',
                '#C62828',
                '#B71C1C',
            ],
            'orange' => [
                '#FFF3E0',
                '#FFE0B2',
                '#FFCC80',
                '#FFB74D',
                '#FFA726',
                '#FF9800',
                '#FB8C00',
                '#F57C00',
                '#EF6C00',
                '#E65100',
            ],
            'yellow' => [
                '#FFFDE7',
                '#FFF9C4',
                '#FFF59D',
                '#FFF176',
                '#FFEE58',
                '#FFEB3B',
                '#FDD835',
                '#FBC02D',
                '#F9A825',
                '#F57F17',
            ],
            'green' => [
                '#E8F5E9',
                '#C8E6C9',
                '#A5D6A7',
                '#81C784',
                '#66BB6A',
                '#4CAF50',
                '#43A047',
                '#388E3C',
                '#2E7D32',
                '#1B5E20',
            ],
            'teal' => [
                '#E0F2F1',
                '#B2DFDB',
                '#80CBC4',
                '#4DB6AC',
                '#26A69A',
                '#009688',
                '#00897B',
                '#00796B',
                '#00695C',
                '#004D40',
            ],
            'blue' => [
                '#E3F2FD',
                '#BBDEFB',
                '#90CAF9',
                '#64B5F6',
                '#42A5F5',
                '#2196F3',
                '#1E88E5',
                '#1976D2',
                '#1565C0',
                '#0D47A1',
            ],
            'purple' => [
                '#F3E5F5',
                '#E1BEE7',
                '#CE93D8',
                '#BA68C8',
                '#AB47BC',
                '#9C27B0',
                '#8E24AA',
                '#7B1FA2',
                '#6A1B9A',
                '#4A148C',
            ],
            'pink' => [
                '#FCE4EC',
                '#F8BBD0',
                '#F48FB1',
                '#F06292',
                '#EC407A',
                '#E91E63',
                '#D81B60',
                '#C2185B',
                '#AD1457',
                '#880E4F',
            ],
            'brown' => [
                '#EFEBE9',
                '#D7CCC8',
                '#BCAAA4',
                '#A1887F',
                '#8D6E63',
                '#795548',
                '#6D4C41',
                '#5D4037',
                '#4E342E',
                '#3E2723',
            ],
            'gray' => [
                '#FAFAFA',
                '#F5F5F5',
                '#EEEEEE',
                '#E0E0E0',
                '#BDBDBD',
                '#9E9E9E',
                '#757575',
                '#616161',
                '#424242',
                '#212121',
            ],
        ]
    ) {
        $this->id ??= Str::uuid()->toString();
    }

    public function render(): View|Closure|string
    {
        return view('flux::components.editor');
    }
}
