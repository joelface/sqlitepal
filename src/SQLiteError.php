<?php

namespace SQLitePal;

/**
 * An exception thrown for errors raised by SQLite3 calls.
 */
class SQLiteError extends \Exception
{
    public function __construct(\Exception $e)
    {
        parent::__construct($e->getMessage(), $e->getCode());
    }
}
