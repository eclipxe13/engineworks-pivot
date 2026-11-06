<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use Throwable;

class PivotExceptionQuery extends PivotException
{
    public function __construct(
        string $message,
        private readonly string $sql,
        Throwable|null $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    public function getSql(): string
    {
        return $this->sql;
    }
}
