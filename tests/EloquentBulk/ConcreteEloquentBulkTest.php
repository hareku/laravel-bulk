<?php

namespace Tests\EloquentBulk;

use Tests\TestCase;
use Mockery;
use Hareku\Bulk\BulkProcessor\BulkProcessor;
use Hareku\Bulk\EloquentBulk\ConcreteEloquentBulk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Connection;
use InvalidArgumentException;

class ConcreteEloquentBulkTest extends TestCase
{
    public function testInsert()
    {
        $modelMock = Mockery::mock(Model::class);

        $modelMock->shouldReceive('usesTimestamps')->once()->andReturn(true);
        $modelMock->shouldReceive('freshTimestamp')->once()->andReturn(new \DateTime('2020-05-10'));
        $modelMock->shouldReceive('getDateFormat')->once()->andReturn('Y-m-d');
        $modelMock->shouldReceive('getCreatedAtColumn')->once()->andReturn('created_at');
        $modelMock->shouldReceive('getUpdatedAtColumn')->once()->andReturn('updated_at');

        $connMock = Mockery::mock(Connection::class);
        $connMock->shouldReceive('getTablePrefix')->once()->andReturn('prefix_');
        $modelMock->shouldReceive('getConnection')->once()->andReturn($connMock);
        $modelMock->shouldReceive('getTable')->once()->andReturn('tbl');

        $procMock = Mockery::mock(BulkProcessor::class);
        $procMock->shouldReceive('insert')
            ->with(
                'prefix_tbl',
                ['name', 'age', 'created_at', 'updated_at'],
                [['john', 22, '2020-05-10', '2020-05-10'], ['james', 23, '2020-05-10', '2020-05-10']]
            )
            ->once();

        $bulk = new ConcreteEloquentBulk($procMock);
        $bulk->insert($modelMock, ['name', 'age'], [['john', 22], ['james', 23]]);
    }

    public function testInsertWithCreatedAtColumn()
    {
        $this->expectException(InvalidArgumentException::class);

        $modelMock = Mockery::mock(Model::class);
        $modelMock->shouldReceive('usesTimestamps')->once()->andReturn(true);
        $modelMock->shouldReceive('getCreatedAtColumn')->once()->andReturn('created_at');

        $procMock = Mockery::mock(BulkProcessor::class);

        $bulk = new ConcreteEloquentBulk($procMock);
        $bulk->insert($modelMock, ['name', 'created_at'], [['john', '2020-01-01']]);
    }

    public function testInsertWithUpdatedAtColumn()
    {
        $this->expectException(InvalidArgumentException::class);

        $modelMock = Mockery::mock(Model::class);
        $modelMock->shouldReceive('usesTimestamps')->once()->andReturn(true);
        $modelMock->shouldReceive('getCreatedAtColumn')->once()->andReturn('created_at');
        $modelMock->shouldReceive('getUpdatedAtColumn')->once()->andReturn('updated_at');

        $procMock = Mockery::mock(BulkProcessor::class);

        $bulk = new ConcreteEloquentBulk($procMock);
        $bulk->insert($modelMock, ['name', 'updated_at'], [['john', '2020-01-01']]);
    }

    public function testUpdate()
    {
        $modelMock = Mockery::mock(Model::class);

        $modelMock->shouldReceive('usesTimestamps')->once()->andReturn(true);
        $modelMock->shouldReceive('freshTimestamp')->once()->andReturn(new \DateTime('2020-05-10'));
        $modelMock->shouldReceive('getDateFormat')->once()->andReturn('Y-m-d');
        $modelMock->shouldReceive('getUpdatedAtColumn')->once()->andReturn('updated_at');

        $connMock = Mockery::mock(Connection::class);
        $connMock->shouldReceive('getTablePrefix')->once()->andReturn('prefix_');
        $modelMock->shouldReceive('getConnection')->once()->andReturn($connMock);
        $modelMock->shouldReceive('getTable')->once()->andReturn('tbl');

        $procMock = Mockery::mock(BulkProcessor::class);
        $procMock->shouldReceive('update')
            ->with('prefix_tbl', ['id'], [
                [
                    'id' => 1,
                    'name' => 'john',
                    'age' => 22,
                    'updated_at' => '2020-05-10',
                ],
                [
                    'id' => 2,
                    'name' => 'james',
                    'age' => 23,
                    'updated_at' => '2020-05-10',
                ],
            ])
            ->once();

        $bulk = new ConcreteEloquentBulk($procMock);
        $bulk->update($modelMock, ['id'], [
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
