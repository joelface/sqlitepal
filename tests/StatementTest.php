<?php

declare(strict_types=1);

namespace SQLitePal\Tests;

use SQLitePal\Statement;
use SQLitePal\StatementNotExecuted;

class StatementTest extends TestCase
{
    protected const SQL = 'SELECT id, name FROM cat WHERE name = ?';

    protected Statement $stmt;

    public function testSqlMethodNoArgs(): void
    {
        $this->assertSame(self::SQL, $this->stmt->sql());
    }

    public function testSqlMethodExpandedBeforeExecuted(): void
    {
        $this->assertSame(
            str_replace('?', 'NULL', self::SQL),
            $this->stmt->sql(true)
        );
    }

    public function testSqlMethodExpandedAfterExecuted(): void
    {
        $this->stmt->execute(['Zelda']);
        $this->assertSame(
            str_replace('?', "'Zelda'", self::SQL),
            $this->stmt->sql(true)
        );
    }

    public function testFetchMethodReturnsArray(): void
    {
        $row = $this->stmt->execute(['Clarence'])->fetch();
        $this->assertSame(['id' => 2, 'name' => 'Clarence'], $row);
    }

    public function testFetchMethodThrowsStatementNotExecuted(): void
    {
        $this->expectException(StatementNotExecuted::class);
        $this->stmt->fetch();
    }

    public function testIterationThrowsStatementNotExecuted(): void
    {
        $this->expectException(StatementNotExecuted::class);
        foreach ($this->stmt as $row) {
            // Exception thrown
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->stmt = $this->db->prepare(self::SQL);
    }
}
