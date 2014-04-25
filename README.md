# Klaus

[![Build Status](https://travis-ci.org/gajus/klaus.png?branch=master)](https://travis-ci.org/gajus/klaus)
[![Coverage Status](https://coveralls.io/repos/gajus/klaus/badge.png?branch=master)](https://coveralls.io/r/gajus/klaus?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/klaus/version.png)](https://packagist.org/packages/gajus/klaus)
[![License](https://poser.pugx.org/gajus/klaus/license.png)](https://packagist.org/packages/gajus/klaus)

User input interpreter for constructing SQL `WHERE` clause. Klaus can build complex `WHERE` clauses of variable depth and with different grouping conditions.

## Documentation

### Preparing Query

Raw query consists of the grouping operator definition (`AND` or `OR`) and condition. There are two types of conditions:

#### Comparison Condition

Comparison consists of user input name, value and the comparison operator, e.g.

```php
[
    'name' => 'foo_name', // User input name
    'value' => '1', // User input value
    'operator' => '=' // Condition operator
]
```

#### Group Condition

The condition itself can define new group, e.g.

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

A complete query must include at least one group and at least one comparison operator.

### Mapping Using Input

Mapping is used to restrict columns that can be included in the query, as well as to provide support for columns that depend on alias or even more complicated constructs.

```sql
SELECT
    `f1`.`name`,
    `b1`.`name`
FROM
    `foo` `f1`
INNER JOIN
    `bar` `b1`
ON
    [..]
```

In the above example, you need to define relation between the parameter name that you are using in the query and the column name in the SQL query, e.g.

```php
$map = [
    'foo_name' => '`f1`.`name`',
    'bar_name' => '`b1`.`name`'
];
```

### Buildng the `WHERE` Clause

The preceeding examples explain how to prepare data for the `Where` constructor.

```php
/**
 * @param array $query
 * @param array $map Map input name to the aliased column in the SQL query, e.g. ['name' => '`p1`.`name`'].
 */
$where = new \Gajus\Klaus\Where($query, $map);
```

We are going to use the SQL from the previous example to construct a prepared statement and execute it.

The `WHERE` clause itself is generated using `getClause` method:

```php
/**
 * @return string SQL WHERE clause representng the query.
 */
$where->getClause();
```

If query does not produce a condition, then `getClause` will always return `1=1`, e.g.

```php
$sql = "
SELECT
    `f1`.`name`,
    `b1`.`name`
FROM
    `foo` `f1`
INNER JOIN
    `bar` `b1`
ON
    [..]
WHERE
    {$where->getClause()}
    ";
```

In the above example, `$sql` is:

```sql
SELECT
    `f1`.`name`,
    `b1`.`name`
FROM
    `foo` `f1`
INNER JOIN
    `bar` `b1`
ON
    [..]
WHERE
    `f1`.`name` = :foo_name_0 AND
    `b1`.`name` = :bar_name_1 AND
        (
            `f1`.`name` = :foo_name_2 OR
            `b1`.`name` = :bar_name_3
        )
```

To execute the query, you have to build [PDOStatement](http://www.php.net/manual/en/class.pdostatement.php), e.g.

```php
$sth = $db->prepare($sql);
```

and execute it using the input data:

```php
/**
 * @return array Input mapped to the prepared statement bindings present in the WHERE clause.
 */
$input = $where->getInput();

$sth->execute($input);
```

In the above example, `$input` is equal to:

```php
[
    'foo_name_0' => '1',
    'bar_name_1' => '2',
    'foo_name_2' => '1',
    'bar_name_3' => '2',
]
```

### Input Template

For basic search you can use `Gajus\Klaus\Where::queryTemplate`.

* Basic query template takes name => value pairs and converts them to `WHERE` clause grouped using `AND`.
* Empty values are discarded.
* Values begning with `%` will use `LIKE` comparison.
* Values endding with `%` will use `LIKE` comparison.
* Values that do not contain `%` or where `%` is not at the begining or end of the query will use `=` comparison.

#### Example

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