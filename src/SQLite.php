<?php

declare(strict_types=1);

namespace SQLitePal;

use SQLite3;
use Traversable;

/**
 * An SQLite3 database connection handler.
 */
class SQLite
{
    public const DEFERRED = 'DEFERRED';
    public const IMMEDIATE = 'IMMEDIATE';
    public const EXCLUSIVE = 'EXCLUSIVE';

    protected SQLite3 $sqlite3;

    public function __construct(string $filename)
    {
        $this->sqlite3 = new SQLite3($filename);
        $this->sqlite3->enableExceptions(true);
        $this->query('PRAGMA foreign_keys = 1');
    }

    /**
     * Begin a transaction.
     */
    public function begin(string $mode = ''): void
    {
        $this->query('BEGIN ' . $mode);
    }

    /**
     * Commit a transaction.
     */
    public function commit(): void
    {
        $this->query('COMMIT');
    }

    /**
     * Rollback a transaction.
     */
    public function rollback(): void
    {
        $this->query('ROLLBACK');
    }

    /**
     * Create a prepared statement.
     */
    public function prepare(string $sql): Statement
    {
        try {
            $stmt = $this->sqlite3->prepare($sql);
            if ($stmt === false) {
                throw new \Exception('Unable to prepare statement');
            }
        } catch (\Exception $e) {
            throw new SQLiteError($e);
        }
        return new Statement($stmt);
    }

    /**
     * Create and execute a prepared statement.
     */
    public function query(string $sql, array $params = []): Statement
    {
        return $this->prepare($sql)->execute($params);
    }

    /**
     * Return an associative array containing the first row of data from a query.
     */
    public function row(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Return a single value from a query.
     * @return mixed
     */
    public function value(string $sql, array $params = [])
    {
        $row = $this->row($sql, $params);
        return current($row);
    }

    /**
     * Return an iterable of the first column from each row of a query.
     */
    public function column(string $sql, array $params = []): Traversable
    {
        foreach ($this->query($sql, $params) as $row) {
            yield current($row);
        }
    }

    /**
     * Execute a query with each set of params and return the number of affected rows.
     */
    public function exec(string $sql, array ...$paramSets): int
    {
        $rowCount = 0;
        $stmt = $this->prepare($sql);
        if (count($paramSets) === 0) {
            $paramSets[] = [];
        }
        foreach ($paramSets as $params) {
            $stmt->execute($params);
            $rowCount += $this->sqlite3->changes();
        }
        return $rowCount;
    }

    /**
     * Backup this database to the destination database.
     */
    public function backup(SQLite $destination): void
    {
        try {
            $this->sqlite3->backup($destination->sqlite3, 'main', 'main');
        } catch (\Exception $e) {
            throw new SQLiteError($e);
        }
    }
}
