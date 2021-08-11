<?php

declare(strict_types=1);

namespace SQLitePal;

use IteratorAggregate;
use SQLite3Stmt;

/**
 * An SQLite3 prepared statement.
 * @implements IteratorAggregate<int, array>
 */
class Statement implements IteratorAggregate
{
    protected SQLite3Stmt $stmt;
    protected ResultSet\ResultSetInterface $result;

    public function __construct(SQLite3Stmt $stmt)
    {
        $this->stmt = $stmt;
        $this->result = new ResultSet\NullResultSet();
    }

    /**
     * Execute the prepared statement.
     */
    public function execute(array $params): self
    {
        try {
            $this->stmt->reset();
            $this->stmt->clear();
            foreach ($params as $param => $value) {
                if (is_int($param)) {
                    $param += 1;
                }
                $this->stmt->bindValue($param, $value);
            }
            $result = $this->stmt->execute();
            if ($result === false) {
                throw new \Exception('Unable to execute statement');
            }
        } catch (\Exception $e) {
            throw new SQLiteError($e);
        }
        $this->result = new ResultSet\ResultSet($result);
        return $this;
    }

    /**
     * Fetch the next row of the result set.
     * @throws NoMoreRows if no more rows available.
     * @throws StatementNotExecuted if the statement hasn't been executed.
     */
    public function fetch(): array
    {
        return $this->result->fetch();
    }

    /**
     * Iterate through each row of the result set.
     * @throws StatementNotExecuted if the statement hasn't been executed.
     */
    public function getIterator()
    {
        try {
            $this->result->reset();
            while (true) {
                yield $this->fetch();
            }
        } catch (NoMoreRows $e) {
            return;
        }
    }

    /**
     * Get the statement's query string.
     * @param bool $expanded Whether to replace param markers with values from previous execution
     */
    public function sql(bool $expanded = false): string
    {
        try {
            return $this->stmt->getSQL($expanded);
        } catch (\Exception $e) {
            throw new SQLiteError($e);
        }
    }
}
