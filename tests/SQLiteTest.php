<?php

declare(strict_types=1);

namespace SQLitePal\Tests;

use SQLitePal\SQLite;
use SQLitePal\Statement;
use SQLitePal\SQLiteError;
use SQLitePal\NoMoreRows;

class SQLiteTest extends TestCase
{
    public function testForeignKeysEnabled(): void
    {
        $this->assertSame(1, $this->db->value('PRAGMA foreign_keys'));
    }

    public function testTransactionWithoutModeCommits(): void
    {
        $this->db->begin();
        $this->db->query('DELETE FROM cat');
        $this->db->commit();
        $this->assertCatCount(0);
    }

    public function testTransactionWithModeCommits(): void
    {
        $this->db->begin(SQLite::IMMEDIATE);
        $this->db->query('DELETE FROM cat');
        $this->db->commit();
        $this->assertCatCount(0);
    }

    public function testbeginWithInvalidModeThrowsError(): void
    {
        $this->expectException(SQLiteError::class);
        $this->db->begin('NOT_A_REAL_MODE');
    }

    public function testRollbackMethodRevertsTransaction(): void
    {
        $this->db->begin();
        $this->db->query('DELETE FROM cat');
        $this->db->rollback();
        $this->assertCatCount(3);
    }

    public function testPrepareMethodReturnsStatement(): void
    {
        $sql = 'SELECT * FROM cat';
        $stmt = $this->db->prepare($sql);
        $this->assertInstanceOf(Statement::class, $stmt);
        $this->assertSame($sql, $stmt->sql());
    }

    public function testPrepareMethodThrowsErrorOnBadSql(): void
    {
        $this->expectException(SQLiteError::class);
        $this->db->prepare('SELECT * FROM dog');
    }

    public function testQueryMethodReturnsStatement(): void
    {
        $result = $this->db->query('SELECT name FROM cat WHERE id = ?', [1]);
        $this->assertInstanceOf(Statement::class, $result);
        $this->assertSame('Grumpy', $result->fetch()['name']);
    }

    public function testRowMethodReturnsArray(): void
    {
        $cat = $this->db->row('SELECT id, name FROM cat WHERE id = ?', [3]);
        $this->assertSame(['id' => 3, 'name' => 'Zelda'], $cat);
    }

    public function testRowMethodThrowsNoMoreRows(): void
    {
        $this->expectException(NoMoreRows::class);
        $this->db->row('SELECT * FROM cat WHERE id = ?', [10]);
        $this->db->query('SELECT * FROM cat WHERE id = ?', [10])->fetch();
    }

    public function testValueMethodReturnsString(): void
    {
        $name = $this->db->value('SELECT name FROM cat WHERE id = ?', [2]);
        $this->assertSame('Clarence', $name);
    }

    public function testValueMethodReturnsInt(): void
    {
        $id = $this->db->value('SELECT id FROM cat WHERE name = ?', ['Zelda']);
        $this->assertSame(3, $id);
    }

    public function testValueMethodThrowsNoMoreRows(): void
    {
        $this->expectException(NoMoreRows::class);
        $this->db->value('SELECT name FROM cat WHERE id = ?', [10]);
    }

    public function testColumnMethodReturnsIterable(): void
    {
        $names = $this->db->column('SELECT name FROM cat WHERE id > ?', [1]);
        $this->assertIsIterable($names);
        $names = iterator_to_array($names);
        $this->assertSame(['Clarence', 'Zelda'], $names);
    }

    public function testExecMethodReturnsRowCount(): void
    {
        $rowCount = $this->db->exec(
            'INSERT INTO cat (name) VALUES (?)',
            ['Francis'],
            ['Gus'],
        );
        $this->assertSame(2, $rowCount);
    }

    public function testBackupMethodSucceeds(): void
    {
        $backup = new SQLite(':memory:');
        $this->db->backup($backup);
        $cats = iterator_to_array($backup->query('SELECT * FROM cat'));
        $this->assertSame([
            ['id' => 1, 'name' => 'Grumpy'],
            ['id' => 2, 'name' => 'Clarence'],
            ['id' => 3, 'name' => 'Zelda'],
        ], $cats);
    }

    public function testBackupMethodFails(): void
    {
        $this->expectException(SQLiteError::class);
        $this->db->query('PRAGMA busy_timeout = ?', [3000]);
        $this->db->begin(SQLite::EXCLUSIVE); // Lock database so backup fails
        $backup = new SQLite(':memory:');
        $this->db->backup($backup);
    }

    protected function assertCatCount(int $expected): void
    {
        $actual = (int) $this->db->value('SELECT COUNT(*) FROM cat');
        $this->assertSame($expected, $actual);
    }
}
