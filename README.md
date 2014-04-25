# Klaus

[![Build Status](https://travis-ci.org/gajus/klaus.png?branch=master)](https://travis-ci.org/gajus/klaus)
[![Coverage Status](https://coveralls.io/repos/gajus/klaus/badge.png?branch=master)](https://coveralls.io/r/gajus/klaus?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/klaus/version.png)](https://packagist.org/packages/gajus/klaus)
[![License](https://poser.pugx.org/gajus/klaus/license.png)](https://packagist.org/packages/gajus/klaus)

Klaus is for constructing SQL query `WHERE` clause based on user input. User input is an array generated using Klaus "advance search" form.

## Input

`WHERE` clause is built from an array input in the following format:

```php
/**
 * @param array $query
 * @param array $map Map input name to the aliased column in the SQL query, e.g. ['name' => '`p1`.`name`'].
 */
$where = new \Gajus\Klaus\Where([
    'group' => 'AND',
    'condition' => [
        ['name' => 'foo', 'value' => '1', 'operator' => '='],
        ['name' => 'bar', 'value' => '2', 'operator' => '='],
        [
            'group' => 'OR',
            'condition' => [
                ['name' => 'foo', 'value' => '1', 'operator' => '='],
                ['name' => 'bar', 'value' => '2', 'operator' => '=']
            ]
        ]
    ]
], [
    'foo' => '`foo`',
    'bar' => '`bar`'
]);
```

### Generating the SQL `WHERE` clause

```php
/**
 * @return string SQL WHERE clause representng the query.
 */
$where->getClause();
```

```sql
`foo` = :foo_0 AND
`bar` = :bar_1 AND
    (
        `foo` = :foo_2 OR
        `bar` = :bar_3
    )
```

### Getting the data associated with the query

```php
/**
 * @return array Input mapped to the prepared statement bindings present in the WHERE clause.
 */
$where->getInput();
```

```php
[
    'foo_0' => '1',
    'bar_1' => '2',
    'foo_2' => '1',
    'bar_3' => '2',
]
```

## Input Template

For basic search you can use `Gajus\Klaus\Where::queryTemplate`.

* Basic query template takes name => value pairs and converts them to `WHERE` clause grouped using `AND`.
* Empty values are discarded.
* Values begning with `%` will use `LIKE` comparison.
* Values endding with `%` will use `LIKE` comparison.
* Values that do not contain `%` or where `%` is not at the begining or end of the query will use `=` comparison.

### Example

```php
$query = \Gajus\Klaus\Where::queryTemplate(['foo' => 'bar', 'baz' => 'qux%']);

// $query is now eq. to:

$query = [
    'group' => 'AND',
    'condition' => [
        ['name' => 'foo', 'value' => 'bar', 'operator' => '='],
        ['name' => 'baz', 'value' => 'qux%', 'operator' => 'LIKE']
    ]
];

// Which you then pass to the Where constructor.

$where = new \Gajus\Klaus\Where($query, ['foo' => '`foo`', 'baz' => '`baz`']);

$sth = $db->prepare("SELECT `foo`, `baz` FROM `quux` WHERE {$where->getClause()}");
$sth->execute($where->getInput());

// ..
```

## Alternatives

[elasticsearch](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html) (ES) provides an API with a query DSL. The only downside of using ES is that it requires data dupliction.