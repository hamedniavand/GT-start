<?php

namespace App\Doctrine;

final class QueryCounter
{
    private int $count = 0;

    public function increment(): void
    {
        ++$this->count;
    }

    public function count(): int
    {
        return $this->count;
    }
}
