# Klaus

[![Build Status](https://travis-ci.org/gajus/klaus.png?branch=master)](https://travis-ci.org/gajus/klaus)
[![Coverage Status](https://coveralls.io/repos/gajus/klaus/badge.png)](https://coveralls.io/r/gajus/klaus)

Klaus is for constructing SQL query `WHERE` clause based on user input. User input is an array generated using Klaus "advance search" form.

## Input

Klaus is expecting input in the following format:

```php
[
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
]
```

The above will produce the following `WHERE` clause:

```sql
`foo` = :foo_0 AND `bar` = :bar_1 AND (`foo` = :foo_2 OR `bar` = :bar_3)
```

along with the input parameters:

```php
[
    'foo_0' => '1',
    'bar_1' => '2',
    'foo_2' => '1',
    'bar_3' => '2',
]
```

The two are used to build and execute a SQL prepared statement.

## Input Template

For basic search you can use `Gajus\Klaus\Where::queryTemplate`.

* Basic query template takes name => value pairs and converts them to `WHERE` clause grouped using `AND`.
* Empty values are discarded.
* Values begning with `%` will use `LIKE` comparison.
* Values endding with `%` will use `LIKE` comparison.
* Values that do not contain `%` or where `%` is not at the begining or end of the query will use `=` comparison.

## Alternatives

[elasticsearch](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html) (ES) provides an API with a query DSL. The only downside of using ES is that it requires data dupliction.