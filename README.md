# Klaus

[![Build Status](https://travis-ci.org/gajus/klaus.png?branch=master)](https://travis-ci.org/gajus/klaus)
[![Coverage Status](https://coveralls.io/repos/gajus/klaus/badge.png?branch=master)](https://coveralls.io/r/gajus/klaus?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/klaus/version.png)](https://packagist.org/packages/gajus/klaus)
[![License](https://poser.pugx.org/gajus/klaus/license.png)](https://packagist.org/packages/gajus/klaus)

User input interpreter for constructing SQL `WHERE` clause. 

## Documentation

Klaus can build complex queries of variable depth using `AND` and `OR` operators.

`WHERE` clause is constructed using:

* Query – user input.
* Map – map of user input variable names to the PDO prepared statement named placeholders.

### Query

Raw query consists of the grouping operator definition (`AND` or `OR`) and condition. There are two types of conditions:

#### Comparison Condition

Comparison consists of user input name, value and the comparison operator.

```php
[
    'name' => 'foo_name', // User input name
    'value' => '1', // User input value
    'operator' => '=' // Condition operator
]
```

#### Group Condition

The condition itself can define new group.

```php
[
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

### Map

Mapping is used to map parameter name in the query to the column name in the SQL statement.

```sql
SELECT
    `f1`.`name`,
    `b1`.`name`
FROM
    `foo` `f1`
INNER JOIN
    `bar` `b1`
```

In the above example, if you intend to allow ``f1`.`name`` and ``b1`.`name`` to be used in the query, you need to define relation between the parameter name that you will use in the query and the column name with the alias.

```php
[
    'foo_name' => '`f1`.`name`',
    'bar_name' => '`b1`.`name`'
];
```