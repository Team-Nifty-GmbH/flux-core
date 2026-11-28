<?php

namespace FluxErp\Support\Html;

use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class EditorAttributeSanitizer implements AttributeSanitizerInterface
{
    protected array $classes = [
        'blade-variable',
        'p-2',
        'underline',
        'text-primary-600',
        'dark:text-primary-400',
        'hover:text-primary-700',
        'dark:hover:text-primary-300',
    ];

    protected array $classPatterns = [
        '/^mention(--[a-z0-9_-]+)?$/',
        '/^language-[a-z0-9_+-]+$/',
    ];

    protected array $styleProperties = [
        'background-color',
        'color',
        'font-size',
        'height',
        'line-height',
        'min-width',
        'text-align',
        'width',
    ];

    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): ?array
    {
        return ['class', 'style'];
    }

    public function sanitizeAttribute(
        string $element,
        string $attribute,
        string $value,
        HtmlSanitizerConfig $config
    ): ?string {
        $sanitized = $attribute === 'class'
            ? $this->sanitizeClass($value)
            : $this->sanitizeStyle($value);

        return $sanitized === '' ? null : $sanitized;
    }

    protected function sanitizeClass(string $value): string
    {
        return collect(preg_split('/\s+/', trim($value)))
            ->filter(fn (string $class): bool => in_array($class, $this->classes, true)
                || array_any($this->classPatterns, fn (string $pattern): bool => preg_match($pattern, $class) === 1)
            )
            ->implode(' ');
    }

    protected function sanitizeStyle(string $value): string
    {
        return collect(explode(';', $value))
            ->map(fn (string $declaration): array => array_map('trim', explode(':', $declaration, 2)))
            ->filter(fn (array $declaration): bool => count($declaration) === 2
                && in_array(Str::lower($declaration[0]), $this->styleProperties, true)
                && preg_match('/^[#a-z0-9.,%()\s-]+$/i', $declaration[1]) === 1
                && ! Str::contains(Str::lower($declaration[1]), ['url', 'expression', 'var('])
            )
            ->map(fn (array $declaration): string => Str::lower($declaration[0]) . ': ' . $declaration[1])
            ->implode('; ');
    }
}
