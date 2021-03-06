<?php

namespace Tests\BulkProcessor;

use Tests\TestCase;
use Mockery;
use Hareku\Bulk\BulkProcessor\PdoBulkProcessor;
use PDO;
use PDOStatement;

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
        $query = $builder->insert("tbl", ['name', 'age'], [['john', 22], ['james', 23]]);
    }

    public function testUpdate()
    {
        $statementMock = Mockery::mock(PDOStatement::class);
        $statementMock->shouldReceive('bindValue')
            ->with(':key_0_index_0', 1)
            ->once();
        $statementMock->shouldReceive('bindValue')
            ->with(':key_1_index_0', 2)
            ->once();

        $statementMock->shouldReceive('execute')
            ->with(['john', 'james', 22, 23])
            ->once();

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')
            ->with(
                'UPDATE `tbl` SET'
                . ' `name` = CASE( WHEN `id` = :key_0_index_0 THEN ? WHEN `id` = :key_1_index_0 THEN ?)'
                . ' `age` = CASE( WHEN `id` = :key_0_index_0 THEN ? WHEN `id` = :key_1_index_0 THEN ?);'
            )
            ->once()
            ->andReturn($statementMock);

        $builder = new PdoBulkProcessor($pdoMock);
        $query = $builder->update("tbl", ['id'], [
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
}
