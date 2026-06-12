<?php

namespace FluxErp\ShareTargetActions;

use InvalidArgumentException;

class ShareTargetActionManager
{
    /** @var array<class-string<ShareTargetAction>> */
    protected array $actions = [];

    /**
     * @return array<class-string<ShareTargetAction>>
     */
    public function all(): array
    {
        return array_values($this->actions);
    }

    public function has(string $action): bool
    {
        return array_key_exists($action, $this->actions);
    }

    public function register(string $action): void
    {
        if (! is_subclass_of($action, ShareTargetAction::class)) {
            throw new InvalidArgumentException(
                'Share target action ' . $action . ' must extend ' . ShareTargetAction::class
            );
        }

        $this->actions[$action] = $action;
    }
}
