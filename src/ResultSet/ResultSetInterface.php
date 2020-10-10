<?php

namespace SQLitePal\ResultSet;

/**
 * An interface for a prepared statement's result set.
 */
interface ResultSetInterface
{
    /**
     * Return an associative array containing the next row of data, or false.
     */
    public function fetch(): array;

    /**
     * Reset the result set back to the first row.
     */
    public function reset(): void;
}
