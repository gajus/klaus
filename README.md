# Klaus

[![Build Status](https://travis-ci.org/gajus/klaus.png?branch=master)](https://travis-ci.org/gajus/klaus)
[![Coverage Status](https://coveralls.io/repos/gajus/klaus/badge.png?branch=master)](https://coveralls.io/r/gajus/klaus?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/klaus/version.png)](https://packagist.org/packages/gajus/klaus)
[![License](https://poser.pugx.org/gajus/klaus/license.png)](https://packagist.org/packages/gajus/klaus)

Klaus is for constructing SQL `WHERE` clause based on user input. User input is an array generated using Klaus "advance search" form.

## How to construct SQL `WHERE` clause using Klaus?

We have the following query:

```sql
SELECT
    `f1`.`name` `foo_name`,
    `b1`.`name` `bar_name`
FROM
    `foo` `f1`
INNER JOIN
    `bar` `b1`
```

We want to use user input to search query using either `foo_name` or `bar_name` column. First, we need to map our input name to the column names as they appear in the SQL query:

```php
$map = [
    'foo_name' => '`f1`.`name`',
    'bar_name' => '`b1`.`name`'
];
```

Then, we need to build the query:

```php
$query = [
    'group' => 'AND',
    'condition' => [
        ['name' => 'foo_name', 'value' => '1', 'operator' => '='],
        ['name' => 'bar_name', 'value' => '2', 'operator' => '='],
        [
            'group' => 'OR',
            'condition' => [
                ['name' => 'foo_name', 'value' => '1', 'operator' => '='],
                ['name' => 'bar_name', 'value' => '2', 'operator' => '=']
            ]
        ]
    ]
]
```

The query itself is an array consisting of group name and conditions, where condition can be a group and condition, [..].

```php
/**
 * @param array $query
 * @param array $map Map input name to the aliased column in the SQL query, e.g. ['name' => '`p1`.`name`'].
 */
$where = new \Gajus\Klaus\Where($query, $map);
```

### Generating the SQL `WHERE` clause

```php
/**
 * @return string SQL WHERE clause representng the query.
 */
$where->getClause();
```

```sql
`f1`.`name` = :foo_name_0 AND
`b1`.`name` = :bar_name_1 AND
    (
        `f1`.`name` = :foo_name_2 OR
        `b1`.`name` = :bar_name_3
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
    'foo_name_0' => '1',
    'bar_name_1' => '2',
    'foo_name_2' => '1',
    'bar_name_3' => '2',
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