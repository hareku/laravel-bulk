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
        $query = $builder->insert('tbl', ['name', 'age'], [['john', 22], ['james', 23]]);
    }

    public function testUpdate()
    {
        $statementMock = Mockery::mock(PDOStatement::class);

        $statementMock->shouldReceive('bindValue')
            ->with(':key_0_index_0', 1)->once();
        $statementMock->shouldReceive('bindValue')
            ->with(':key_1_index_0', 2)->once();

        $statementMock->shouldReceive('bindValue')
            ->with(':key_0_column_name', 'john')->once();
        $statementMock->shouldReceive('bindValue')
            ->with(':key_1_column_name', 'james')->once();

        $statementMock->shouldReceive('bindValue')
            ->with(':key_0_column_age', 22)->once();
        $statementMock->shouldReceive('bindValue')
            ->with(':key_1_column_age', 23)->once();

        $statementMock->shouldReceive('execute')->once();

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('prepare')
            ->with(
                'UPDATE `tbl` SET'
                . ' `name` = (CASE WHEN `id` = :key_0_index_0 THEN :key_0_column_name WHEN `id` = :key_1_index_0 THEN :key_1_column_name ELSE `name` END)'
                . ', `age` = (CASE WHEN `id` = :key_0_index_0 THEN :key_0_column_age WHEN `id` = :key_1_index_0 THEN :key_1_column_age ELSE `age` END)'
                . ' WHERE `id` IN (:key_0_index_0,:key_1_index_0);'
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
}
