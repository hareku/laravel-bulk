# laravel-bulk

[![testing](https://github.com/hareku/laravel-bulk/actions/workflows/testing.yml/badge.svg)](https://github.com/hareku/laravel-bulk/actions/workflows/testing.yml)

`laravel-bulk` provides functions that execute bulk insert/update.

Packagist: https://packagist.org/packages/hareku/laravel-bulk

## Installation

Composer: `composer require hareku/laravel-bulk`.

## Usage

### EloquentBulk

`EloquentBulk` provides bulk functions for Eloquent models.

```php

use Hareku\Bulk\Facades\EloquentBulk;

// \Illuminate\Database\Eloquent\Model
$model = new \App\Models\User;

$columns = ['name', 'age'];
$records = [
    ['john', 22],
    ['james', 23],
];

// EloquentBulk automatically resolves the table name,
// and fills `created_at` and `updated_at` columns.
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

EloquentBulk::update($model, $indices, $newRecords);

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

## Contribution

Please open an issue or a pull request.

### Local development

You can develop on VSCode remote container that already installed PHP and other dependencies. Otherwise, you have to install PHP and composer to the local, or run tests on Github Actions.

- Install dependencies: `composer install`
- Test with PHPUnit: `composer test`
- Test with PHPUnit and Xdebug coverage: `composer test:coverage-text` or `composer test:coverage-html`
