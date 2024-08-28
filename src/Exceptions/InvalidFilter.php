<?php

namespace FluxErp\Exceptions;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidFilter extends HttpException
{
    public function __construct($message)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    public static function invalidFilterScheme(string $item, string $filter): static
    {
        if ($filter === 'between') {
            $scheme = 'column|value1;value2';
        } else {
            $scheme = 'column|operator|value';
        }

        $message = 'Invalid Filter scheme: expected: ' . $scheme . ', requested: \'' . $item . '\'';

        return new static($message);
    }

    public static function filterNotAllowed(string $column, Collection $allowed): static
    {
        $message = 'Requested filter \'' . $column . '\' not allowed. Allowed filters are \'' .
            $allowed->implode(', ') . '\'';

        return new static($message);
    }

    public static function operatorNotAllowed(string $operator, array $allowed): static
    {
        $message = 'Requested operator \'' . $operator . '\' not allowed. Allowed operators are \'' .
            implode(', ', $allowed) . '\'';

        return new static($message);
    }
}
