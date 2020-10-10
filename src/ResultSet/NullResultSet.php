<?php

declare(strict_types=1);

namespace SQLitePal\ResultSet;

use SQLitePal\StatementNotExecuted;

/**
 * A result set for an unexecuted statement.
 */
class NullResultSet implements ResultSetInterface
{
    public function fetch(): array
    {
        throw new StatementNotExecuted();
    }

    public function reset(): void
    {
        throw new StatementNotExecuted();
    }
}
