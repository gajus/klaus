<?php
/**
 * 
 */
class WhereTest extends PHPUnit_Framework_TestCase {
	public function testNoCondition () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`'], []);

		$this->assertSame('1=1', $where->getClause());
		$this->assertSame([], $where->getInput());
	}

	public function testEmptyCondition () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`'], ['group' => 'AND', 'condition' => []]);

		$this->assertSame('1=1', $where->getClause());
		$this->assertSame([], $where->getInput());
	}

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

	public function testNestedCondition () {
		$where = new \Gajus\Klaus\Where(['foo' => '`foo`', 'bar' => '`bar`'], [
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => '1', 'operation' => '='],
				['name' => 'bar', 'value' => '1', 'operation' => '='],
				[
					'group' => 'OR',
					'condition' => [
						['name' => 'foo', 'value' => '1', 'operation' => '='],
						['name' => 'bar', 'value' => '1', 'operation' => '=']
					]
				]
			]
		]);

		$this->assertSame('`foo` = :foo_0 AND `bar` = :bar_1 AND (`foo` = :foo_2 OR `bar` = :bar_3)', $where->getClause());
		$this->assertSame(['foo_0' => '1', 'bar_1' => '1', 'foo_2' => '1', 'bar_3' => '1'], $where->getInput());
	}
}