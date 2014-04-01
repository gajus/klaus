<?php
/**
 * 
 */
class WhereTest extends PHPUnit_Framework_TestCase {

	public function testSingleParameter () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`'], [
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => 'bar', 'operation' => '=']
			]
		]);

		$this->assertSame('`foo` = :foo_0', $where->getClause());
		$this->assertSame(['foo_0' => 'bar'], $where->getInput());
	}

	public function testAndGroup () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`', 'bar' => '`bar`'], [
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => '1', 'operation' => '='],
				['name' => 'bar', 'value' => '1', 'operation' => '=']
			]
		]);

		$this->assertSame('`foo` = :foo_0 AND `bar` = :bar_1', $where->getClause());
		$this->assertSame(['foo_0' => '1', 'bar_1' => '1'], $where->getInput());
	}

	public function testOrGroup () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`', 'bar' => '`bar`'], [
			'group' => 'OR',
			'condition' => [
				['name' => 'foo', 'value' => '1', 'operation' => '='],
				['name' => 'bar', 'value' => '1', 'operation' => '=']
			]
		]);

		$this->assertSame('`foo` = :foo_0 OR `bar` = :bar_1', $where->getClause());
		$this->assertSame(['foo_0' => '1', 'bar_1' => '1'], $where->getInput());
	}

	/**
	 * @expectedException Gajus\Klaus\Exception\LogicException
	 * @expectedExceptionMessage Unexpected group condition.
	 */
	public function testInvalidGroupCondition () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`', 'bar' => '`bar`'], [
			'group' => 'XXX',
			'condition' => [
				['name' => 'foo', 'value' => '1', 'operation' => '='],
				['name' => 'bar', 'value' => '1', 'operation' => '=']
			]
		]);
	}

	/**
	 * @expectedException Gajus\Klaus\Exception\LogicException
	 * @expectedExceptionMessage Invalid group.
	 */
	public function testInvalidGroup () {
		$where = new \Gajus\Klaus\Where([], [
			'group' => 'AND'
		]);
	}

	/**
	 * @expectedException Gajus\Klaus\Exception\LogicException
	 * @expectedExceptionMessage Invalid input condition.
	 */
	public function testInvalidInputCondition () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`', 'bar' => '`bar`'], [
			'group' => 'AND',
			'condition' => [
				['name' => 'foo']
			]
		]);
	}

	/**
	 * @expectedException Gajus\Klaus\Exception\LogicException
	 * @expectedExceptionMessage Not mapped input condition.
	 */
	public function testNotMappedInput () {
		$where = new \Gajus\Klaus\Where([], [
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => '1', 'operation' => '=']
			]
		]);
	}



	/*
	[
    'group' => 'AND',
    'condition' => [
        ['name' => 'name', 'value' => 'foo', 'operation' => '='],
        ['name' => 'duration', 'value' => 'bar', 'operation' => '='],
        [
            'group' => 'OR',
            'condition' => [
                ['name' => 'amount', 'value' => 10, 'operation' => '>'],
                ['name' => 'amount', 'value' => 100, 'operation' => '<'],
            ]
        ]
    ]
]*/

	/*public function testNoParameters () {
		$where = new \Gajus\Klaus\Where();

		$this->assertCount(0, $where->getData());
		$this->assertSame('1=1', $where->getQuery());
	}

	public function testOneParameter () {
		$where = new \Gajus\Klaus\Where(['user_first_name' => '`u1`.`first_name`'], ['user_first_name' => 'foo']);

		$this->assertCount(1, $where->getData());
		$this->assertArrayHasKey('user_first_name', $where->getData());
		$this->assertSame(['user_first_name' => 'foo'], $where->getData());
		$this->assertSame('1=1 AND `u1`.`first_name` = :user_first_name', $where->getQuery());
	}

	public function testTwoParameters () {
		$where = new \Gajus\Klaus\Where(['user_first_name' => '`u1`.`first_name`', 'user_last_name' => '`u1`.`last_name`'], ['user_first_name' => 'foo', 'user_last_name' => 'bar']);

		$this->assertCount(2, $where->getData());
		$this->assertArrayHasKey('user_first_name', $where->getData());
		$this->assertArrayHasKey('user_last_name', $where->getData());
		$this->assertSame(['user_first_name' => 'foo', 'user_last_name' => 'bar'], $where->getData());
		$this->assertSame('1=1 AND `u1`.`first_name` = :user_first_name AND `u1`.`last_name` = :user_last_name', $where->getQuery());
	}

	public function testTwoParametersOneEmpty () {
		$where = new \Gajus\Klaus\Where(['user_first_name' => '`u1`.`first_name`', 'user_last_name' => '`u1`.`last_name`'], ['user_first_name' => 'foo', 'user_last_name' => '']);

		$this->assertCount(2, $where->getData());
		$this->assertArrayHasKey('user_first_name', $where->getData());
		$this->assertArrayNotHasKey('user_last_name', $where->getData());
		$this->assertSame(['user_first_name' => 'foo'], $where->getData());
		$this->assertSame('1=1 AND `u1`.`first_name` = :user_first_name', $where->getQuery());
	}

	public function testWildcard () {
		$where = new \Gajus\Klaus\Where(['user_first_name' => '`u1`.`first_name`'], ['user_first_name' => 'foo*']);

		$this->assertCount(2, $where->getData());
		$this->assertArrayHasKey('user_first_name', $where->getData());
		$this->assertSame(['user_first_name' => 'foo%'], $where->getData());
		$this->assertSame('1=1 AND `u1`.`first_name` LIKE :user_first_name', $where->getQuery());
	}

	public function testEscapedWildcard () {
		$where = new \Gajus\Klaus\Where(['user_first_name' => '`u1`.`first_name`'], ['user_first_name' => 'foo\*']);

		$this->assertCount(2, $where->getData());
		$this->assertArrayHasKey('user_first_name', $where->getData());
		$this->assertSame(['user_first_name' => 'foo*'], $where->getData());
		$this->assertSame('1=1 AND `u1`.`first_name` = :user_first_name', $where->getQuery());
	}*/
}