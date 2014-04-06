<?php
/**
 * 
 */
class WhereTemplateTest extends PHPUnit_Framework_TestCase {
	public function testEmptyCondition () {
		$query = \Gajus\Klaus\Where::queryTemplate([]);

		$this->assertSame([
			'group' => 'AND',
			'condition' => []
		], $query);
	}

	public function testEmptyParameterCondition () {
		$query = \Gajus\Klaus\Where::queryTemplate(['foo' => '']);

		$this->assertSame([
			'group' => 'AND',
			'condition' => []
		], $query);
	}

	public function testSingleParameterCondition () {
		$query = \Gajus\Klaus\Where::queryTemplate(['foo' => 'bar']);

		$this->assertSame([
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => 'bar', 'operator' => '=']
			]
		], $query);
	}

	public function testBeginsWithWildcard () {
		$query = \Gajus\Klaus\Where::queryTemplate(['foo' => '%bar']);

		$this->assertSame([
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => '%bar', 'operator' => 'LIKE']
			]
		], $query);
	}

	public function testEndsWithWildcard () {
		$query = \Gajus\Klaus\Where::queryTemplate(['foo' => 'bar%']);

		$this->assertSame([
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => 'bar%', 'operator' => 'LIKE']
			]
		], $query);
	}

	public function testContainsWithWildcard () {
		$query = \Gajus\Klaus\Where::queryTemplate(['foo' => '%b%a%r%']);

		$this->assertSame([
			'group' => 'AND',
			'condition' => [
				['name' => 'foo', 'value' => '%b%a%r%', 'operator' => '=']
			]
		], $query);
	}
}