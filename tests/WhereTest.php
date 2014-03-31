<?php
/**
 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
 */
class WhereTest extends PHPUnit_Framework_TestCase {
	public function testNoParameters () {
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
	}
}