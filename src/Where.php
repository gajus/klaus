<?php
namespace Gajus\Klaus;

/**
 * @link https://github.com/gajus/klaus for the canonical source repository
 * @license https://github.com/gajus/klaus/blob/master/LICENSE BSD 3-Clause
 */
class Where {
	private
		/**
		 * @var array $map Map input value to the query parameter.
		 */
		$map,
		/**
		 * @var array $query Input that is matched against the $map.
		 */
		$query,
		/**
		 *
		 */
		$clause,
		/**
		 *
		 */
		$input;

	
	/*public function __construct (array $map, array $input) {
		$this->map = $map;
		$this->input = $input;
	}*/

	public function __construct (array $map, array $query) {
		$this->map = $map;
		$this->query = $query;

		$this->clause = $this->buildGroup($this->query);
	}

	/**
	 * @return string
	 */
	public function getClause () {
		return $this->clause;
	}

	/**
	 * @return array
	 */
	public function getInput () {
		return $this->input;
	}

	/**
	 * @return string
	 */
	private function buildGroup ($group) {
		$clause = [];

		if (!isset($group['group'], $group['condition'])) {
			throw new Exception\LogicException('Invalid group.');
		}

		if ($group['group'] !== 'AND' && $group['group'] !== 'OR') {
			throw new Exception\LogicException('Unexpected group condition.');
		}

		foreach ($group['condition'] as $condition) {
			if (isset($condition['group'])) {
				$clause[] = '(' . $this->buildGroup($condition['group']) . ')';
			} else {
				if (!isset($condition['name'], $condition['value'], $condition['operation'])) {
					throw new Exception\LogicException('Invalid input condition.');
				}

				if (!isset($this->map[$condition['name']])) {
					throw new Exception\LogicException('Not mapped input condition.');
				}

				$clause[] = $this->map[$condition['name']] . ' ' . $condition['operation'] . ' :' . $condition['name'] . '_' . count($this->input);

				$this->input[$condition['name'] . '_' . count($this->input)] = $condition['value'];
			}
		}

		return implode(' ' . $group['group'] . ' ', $clause);
	}
}