<?php

namespace FluxErp\DataType;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * Handle serialization of DateTimeInterface objects.
 */
class DateTimeHandler implements HandlerInterface
{
    /**
     * The date format to use for serializing.
     */
    protected string $format = 'Y-m-d H:i:s.uO';

    public function getDataType(): string
    {
        return 'datetime';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function serializeValue(mixed $value): string
    {
        if ($value === '') {
            return '';
        }

        return Carbon::parse($value)->format($this->format);
    }

    public function unserializeValue(?string $serializedValue): bool|null|Carbon
    {
        if (is_null($serializedValue) || $serializedValue === '') {
            return null;
        }

        return Carbon::createFromFormat($this->format, $serializedValue);
    }
}
