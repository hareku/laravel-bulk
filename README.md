# laravel-bulk

`laravel-bulk` provides functions that execute bulk insert/update.

## Installation

Composer: `composer require hareku/laravel-bulk`.

- Supporting Laravel version is `"^6.0|^7.0|^8.0"`.
- Supporting PHP version is `"^7.2|^8.0"`.

## Usage

### EloquentBulk

`EloquentBulk` provides bulk functions for Eloquent models.

```php

use Hareku\Bulk\Facades\EloquentBulk;

// EloquentBulk automatically resolves the table name,
// and fills `created_at` and `updated_at` columns
// from the given model instance.
$model = new User;

$columns = ['name', 'age'];
$records = [
    ['john', 22],
    ['james', 23],
];

EloquentBulk::insert($model, $columns, $records);

dump(User::all());
// [
//     {
//         id: 1,
//         name: john,
//         age: 22,
//         created_at: 2020-01-01 12:00:00,
//         updated_at: 2020-01-01 12:00:00
//     },
//     {
//         id: 2,
//         name: james,
//         age: 23,
//         created_at: 2020-01-01 12:00:00,
//         updated_at: 2020-01-01 12:00:00
//     },
// ]

$indices = ['id'];

// each record must have indices for sql's WHERE conditions.
$newRecords = [
    [
        'id' => 1,
        'name' => 'new_john',
        'age' => 25,
    ],
    [
        'id' => 2,
        'name' => 'new_james',
    ],
];

EloquentBulk::update($model, $indices, $records);

dump(User::all());
// [
//     {
//         id: 1,
//         name: new_john,
//         age: 25,
//         created_at: 2020-01-01 12:00:00,
//         updated_at: 2020-12-01 12:00:00
//     },
//     {
//         id: 2,
//         name: new_james,
//         age: 23,
//         created_at: 2020-01-01 12:00:00,
//         updated_at: 2020-12-01 12:00:00
//     },
// ]

```

### BulkProcessor

`BulkProcessor` provides functions without Eloquent features (auto resolving table names, and filling timestamp columns).
So you have to give the table name.

```php

use Hareku\Bulk\Facades\BulkProcessor;

BulkProcessor::insert($tableName, $columns, $records);
BulkProcessor::update($tableName, $indices, $records);

```
