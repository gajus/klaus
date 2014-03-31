<?php
namespace Gajus\Klaus;

/**
 * @link https://github.com/gajus/klaus for the canonical source repository
 * @license https://github.com/gajus/klaus/blob/master/LICENSE BSD 3-Clause
 */
class Where implements \ArrayAccess {
	private
		/**
		 * @var array $map Map input value to the query parameter.
		 */
		$map,
		/**
		 * @var array $input Input that is matched against the $map.
		 */
		$input;

	
	public function __construct (array $map, array $input) {
		$this->map = $map;
		$this->input = $input;
	}

	/**
	 * @return string WHERE clause.
	 */
	public function getClause () {
		$clause = ['1=1'];

		foreach ($this->map as $name => $column) {
			$clause[] = $column .' LIKE :' . $name;
		}

		return implode(' AND ', $clause);
	}

	/**
	 * @return array Input parameters that matched the $map clause.
	 */
	public function getInput () {
		$data = [];

		foreach ($this->map as $name) {
			if (isset($this->input))
		}
	}
}