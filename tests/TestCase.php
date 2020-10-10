<?php

declare(strict_types=1);

namespace SQLitePal\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use SQLitePal\SQLite;

class TestCase extends PHPUnitTestCase
{
    protected SQLite $db;

    protected function setUp(): void
    {
        $this->db = new SQLite(':memory:');
        $this->db->exec('
            CREATE TABLE cat (
                id INTEGER PRIMARY KEY,
                name TEXT
            )
        ');
        $this->db->exec(
            'INSERT INTO cat (name) VALUES (?)',
            ['Grumpy'],
            ['Clarence'],
            ['Zelda'],
        );
    }
}
