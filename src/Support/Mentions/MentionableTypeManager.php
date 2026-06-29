<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Traits\Model\Mentionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Throwable;

class MentionableTypeManager
{
    use Macroable;

    /**
     * @var array<string, class-string>
     */
    protected array $record = [];

    /**
     * @var array<string, class-string>
     */
    protected array $user = [];

    public function autoDiscover(): void
    {
        if (! function_exists('get_models_with_trait')) {
            return;
        }

        foreach (get_models_with_trait(Mentionable::class, fn (string $class): string => $class) as $class) {
            try {
                $this->register($class);
            } catch (Throwable) {
                // Don't throw exceptions on auto discovery
            }
        }
    }

    /**
     * @param  class-string  $class
     */
    public function register(string $class): void
    {
        if (! in_array(Mentionable::class, class_uses_recursive($class), true)) {
            throw new InvalidArgumentException(
                "The provided class '{$class}' does not use the Mentionable trait."
            );
        }

        $key = $class::mentionTypeKey();

        if ($class::mentionType() === MentionTypeEnum::User) {
            $this->user[$key] = $class;
        } else {
            $this->record[$key] = $class;
        }
    }

    public function unregister(string $key): void
    {
        unset($this->record[$key], $this->user[$key]);
    }

    /**
     * @return ($keysOnly is true ? array<int, string> : array<string, class-string>)
     */
    public function all(bool $keysOnly = false): array
    {
        $types = $this->record + $this->user;

        return $keysOnly ? array_keys($types) : $types;
    }

    /**
     * @return ($keysOnly is true ? array<int, string> : array<string, class-string>)
     */
    public function getRecordMentionableTypes(bool $keysOnly = false): array
    {
        return $keysOnly ? array_keys($this->record) : $this->record;
    }

    /**
     * @return ($keysOnly is true ? array<int, string> : array<string, class-string>)
     */
    public function getUserMentionableTypes(bool $keysOnly = false): array
    {
        return $keysOnly ? array_keys($this->user) : $this->user;
    }
}
