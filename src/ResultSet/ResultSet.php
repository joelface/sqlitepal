<?php

declare(strict_types=1);

namespace SQLitePal\ResultSet;

use SQLite3Result;
use SQLitePal\NoMoreRows;

/**
 * A ResultSetInterface-compliant wrapper for SQLite3Result objects.
 */
class ResultSet implements ResultSetInterface
{
    protected SQLite3Result $result;

    public function __construct(SQLite3Result $result)
    {
        $this->result = $result;
    }

    public function fetch(): array
    {
        $row = $this->result->fetchArray(SQLITE3_ASSOC);
        if ($row === false) {
            throw new NoMoreRows();
        }
        return $row;
    }

    public function reset(): void
    {
        $this->result->reset();
    }
}
