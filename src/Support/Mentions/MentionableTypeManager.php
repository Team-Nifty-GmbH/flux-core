<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Traits\Model\Mentionable;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        foreach (Relation::morphMap() as $class) {
            try {
                $class = resolve_static($class, 'class');

                if (in_array(Mentionable::class, class_uses_recursive($class), true)) {
                    $this->register($class);
                }
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
        $class = resolve_static($class, 'class');

        if (! in_array(Mentionable::class, class_uses_recursive($class), true)) {
            throw new InvalidArgumentException(
                "The provided class '{$class}' does not use the Mentionable trait."
            );
        }

        $key = $class::mentionTypeKey();

        $this->unregister($key);

        if ($class::mentionType() === MentionTypeEnum::User) {
            $this->user[$key] = $class;
        } else {
            $this->record[$key] = $class;
        }
    }

    public function unregister(string $key, ?string $mentionType = null): void
    {
        if (is_null($mentionType) || $mentionType === MentionTypeEnum::Record) {
            unset($this->record[$key]);
        }

        if (is_null($mentionType) || $mentionType === MentionTypeEnum::User) {
            unset($this->user[$key]);
        }
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
