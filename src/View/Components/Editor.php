<?php

namespace FluxErp\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

class Editor extends Component
{
    public static array $colorPalette = [
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
    ];

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
        public ?array $textColors = null,
        public ?array $textBackgroundColors = null,

        public bool $bladeSupport = false,
        public ?string $bladeModel = null,
        public ?array $bladeModelData = null,
        public array $bladeVariables = []
    ) {
        $this->id ??= Str::uuid()->toString();
        $this->textColors ??= static::$colorPalette;
        $this->textBackgroundColors ??= static::$colorPalette;

        if (! empty($this->bladeVariables)) {
            $this->bladeSupport = true;
            $this->parseBladeVariables();
        } elseif ($this->bladeSupport && $this->bladeModel) {
            $this->bladeModelData = $this->extractModelData($this->bladeModel);
        }
    }

    public function render(): View|Closure|string
    {
        return view('flux::components.editor');
    }

    protected function parseBladeVariables(): void
    {
        $parsedVariables = [];

        foreach ($this->bladeVariables as $variableName => $modelClass) {
            if (class_exists($modelClass)) {
                $modelData = $this->extractModelData($modelClass);
                $modelData['variableName'] = $variableName;
                $parsedVariables[] = $modelData;
            }
        }

        $this->bladeModelData = $parsedVariables;
    }

    protected function extractModelData(string $modelClass): array
    {
        if (! class_exists($modelClass)) {
            return ['name' => '', 'attributes' => [], 'methods' => []];
        }

        $reflection = new ReflectionClass($modelClass);
        $instance = app($modelClass);

        $attributes = [];
        $methods = [];

        if (method_exists($instance, 'getFillable')) {
            foreach ($instance->getFillable() as $fillable) {
                $attributes[] = [
                    'name' => $fillable,
                    'type' => 'attribute',
                    'description' => __('Model attribute: :name', ['name' => $fillable]),
                    'displayName' => __(Str::headline($fillable)),
                ];
            }
        }

        if (method_exists($instance, 'getCasts')) {
            foreach (array_keys($instance->getCasts()) as $cast) {
                if (! in_array($cast, array_column($attributes, 'name'))) {
                    $attributes[] = [
                        'name' => $cast,
                        'type' => 'cast',
                        'description' => __('Model cast: :name', ['name' => $cast]),
                        'displayName' => __(Str::headline($cast)),
                    ];
                }
            }
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $modelClass &&
                ! $method->isStatic() &&
                ! str_starts_with($method->name, '_') &&
                ! in_array($method->name, ['save', 'delete', 'update', 'create'])) {
                $params = [];
                foreach ($method->getParameters() as $param) {
                    $paramType = 'mixed';
                    if ($param->getType()) {
                        $type = $param->getType();
                        if ($type instanceof ReflectionNamedType) {
                            $paramType = $type->getName();
                        } elseif ($type instanceof ReflectionUnionType) {
                            $paramType = implode('|', array_map(fn ($t) => $t->getName(), $type->getTypes()));
                        }
                    }

                    if ($param->isOptional()) {
                        try {
                            $defaultValue = $param->getDefaultValue();
                            $defaultStr = is_null($defaultValue) ? 'null' : var_export($defaultValue, true);
                            $params[] = "{$paramType} \${$param->name} = {$defaultStr}";
                        } catch (ReflectionException $e) {
                            $params[] = "{$paramType} \${$param->name} = null";
                        }
                    } else {
                        $params[] = "{$paramType} \${$param->name}";
                    }
                }

                $methods[] = [
                    'name' => $method->name,
                    'type' => 'method',
                    'parameters' => $params,
                    'description' => __('Model method: :name(:params)', [
                        'name' => $method->name,
                        'params' => implode(', ', $params),
                    ]),
                    'displayName' => __(Str::headline($method->name)),
                ];
            }
        }

        $variableName = Str::camel(class_basename($modelClass));

        return [
            'name' => class_basename($modelClass),
            'fullName' => $modelClass,
            'variableName' => $variableName,
            'attributes' => $attributes,
            'methods' => $methods,
        ];
    }
}
