<?php

namespace FluxErp\Support\Mentions;

readonly class MentionState
{
    protected const COLOR_SHADE = '500';

    public function __construct(
        public string $label,
        public string $color,
    ) {}

    public function cssColor(): string
    {
        if (preg_match('/^[a-z]+$/', $this->color) !== 1) {
            return 'currentColor';
        }

        return 'var(--color-' . $this->color . '-' . self::COLOR_SHADE . ')';
    }

    public function toPillAttributes(): string
    {
        $label = e($this->label);

        return sprintf(
            ' data-mention-state="%s" title="%s" style="--mention-state-color: %s"',
            $label,
            $label,
            e($this->cssColor()),
        );
    }
}
