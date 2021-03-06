<?php

namespace Tests\BulkProcessor;

use Tests\TestCase;
use Mockery;
use Hareku\Bulk\BulkProcessor\PdoBulkProcessor;
use PDO;
use PDOStatement;
use InvalidArgumentException;

class PdoBulkProcessorTest extends TestCase
{
    public function testInsert()
    {
        $statementMock = Mockery::mock(PDOStatement::class);
        $statementMock->shouldReceive('execute')
            ->with(['john', 22, 'james', 23])
            ->once();

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')
            ->with('INSERT INTO `tbl` (name,age) VALUES (?,?),(?,?);')
            ->once()
            ->andReturn($statementMock);

        $builder = new PdoBulkProcessor($pdoMock);
        $query = $builder->insert('tbl', ['name', 'age'], [['john', 22], ['james', 23]]);
    }

    public function testInsertWithEmptyColumns()
    {
        $this->expectException(InvalidArgumentException::class);

        $pdoMock = Mockery::mock(PDO::class);
        $builder = new PdoBulkProcessor($pdoMock);
        $builder->insert('tbl', [], [['john', 22]]);
    }

    public function testInsertWithEmptyRecords()
    {
        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')->times(0);

        $builder = new PdoBulkProcessor($pdoMock);
        $builder->insert('tbl', ['name', 'age'], []);
    }

    public function testUpdate()
    {
        $statementMock = Mockery::mock(PDOStatement::class);
        $statementMock->shouldReceive('execute')
            ->with([1, 'john', 2, 'james', 1, 22, 2, 23, 1, 2])
            ->once();

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')
            ->with(
                'UPDATE `tbl` SET'
                . ' `name` = (CASE WHEN `id` = ? THEN ? WHEN `id` = ? THEN ? ELSE `name` END)'
                . ', `age` = (CASE WHEN `id` = ? THEN ? WHEN `id` = ? THEN ? ELSE `age` END)'
                . ' WHERE `id` IN (?,?);'
            )
            ->once()
            ->andReturn($statementMock);

        $builder = new PdoBulkProcessor($pdoMock);
        $query = $builder->update('tbl', ['id'], [
            [
                'id' => 1,
                'name' => 'john',
                'age' => 22,
            ],
            [
                'id' => 2,
                'name' => 'james',
                'age' => 23,
            ],
        ]);
    }

    public function testUpdateWithMultipleIndices()
    {
        $statementMock = Mockery::mock(PDOStatement::class);
        $statementMock->shouldReceive('execute')
            ->with([1, 'john', 22, 1, 'john'])
            ->once();

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')
            ->with(
                'UPDATE `tbl` SET'
                . ' `age` = (CASE WHEN `id` = ? AND `name` = ? THEN ? ELSE `age` END)'
                . ' WHERE `id` IN (?) AND `name` IN (?);'
            )
            ->once()
            ->andReturn($statementMock);

        $builder = new PdoBulkProcessor($pdoMock);
        $query = $builder->update('tbl', ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'john',
                'age' => 22,
            ],
        ]);
    }

    public function testUpdateWithSparseRecords()
    {
        $statementMock = Mockery::mock(PDOStatement::class);
        $statementMock->shouldReceive('execute')
            ->with([1, 'john', 2, 'james', 1, 22, 1, 2])
            ->once();

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')
            ->with(
                'UPDATE `tbl` SET'
                . ' `name` = (CASE WHEN `id` = ? THEN ? WHEN `id` = ? THEN ? ELSE `name` END)'
                . ', `age` = (CASE WHEN `id` = ? THEN ? ELSE `age` END)'
                . ' WHERE `id` IN (?,?);'
            )
            ->once()
            ->andReturn($statementMock);

        $builder = new PdoBulkProcessor($pdoMock);
        $query = $builder->update('tbl', ['id'], [
            [
                'id' => 1,
                'name' => 'john',
                'age' => 22,
            ],
            [
                'id' => 2,
                'name' => 'james',
            ],
        ]);
    }
}
