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
		 * @var string
		 */
		$clause = '1=1',
		/**
		 * @var array
		 */
		$input = [];

	/**
	 * @param array $query
	 * @param array $map Map input name to the aliased column in the SQL query, e.g. ['name' => '`p1`.`name`'].
	 */
	public function __construct (array $query, array $map) {
		$this->map = $map;
		$this->query = $query;

		if ($query) {
			$this->clause = $this->buildGroup($this->query);
		}
	}

	/**
	 * @return string SQL WHERE clause representng the query.
	 */
	public function getClause () {
		return $this->clause;
	}

	/**
	 * @return array Input mapped to the prepared statement bindings present in the WHERE clause.
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

		if (empty($group['condition'])) {
			return '1=1';
		}

		foreach ($group['condition'] as $condition) {
			if (isset($condition['group'])) {
				$clause[] = '(' . $this->buildGroup($condition) . ')';
			} else {
				if (!isset($condition['name'], $condition['value'], $condition['operator'])) {
					throw new Exception\LogicException('Invalid input condition.');
				}

				if (!isset($this->map[$condition['name']])) {
					throw new Exception\LogicException('Not mapped input condition.');
				}

				if (!in_array($condition['operator'], ['=', 'LIKE', '>', '<', '>=', '<=', true])) {
					throw new Exception\UnexpectedValueException('Invalid comparison operator.');
				}

				$clause[] = $this->map[$condition['name']] . ' ' . $condition['operator'] . ' :' . $condition['name'] . '_' . count($this->input);

				$this->input[$condition['name'] . '_' . count($this->input)] = $condition['value'];
			}
		}

		return implode(' ' . $group['group'] . ' ', $clause);
	}

	/**
	 * @param array $input
	 * @param string $template Template name.
	 * @return array
	 */
	static public function queryTemplate (array $input, $template = 'simple') {
		if ($template != 'simple') {
			throw new Exception\UnexpectedValueException('Unrecognised template.');
		}

		$input = array_filter($input);

		$query = [
		    'group' => 'AND',
		    'condition' => []
		];

		foreach ($input as $name => $value) {
			$condition = ['name' => $name, 'value' => $value, 'operator' => '='];

			if (strpos(mb_substr($value, 1, -1), '%') !== false) {

			} else if (mb_strpos($value, '%') === 0 ||  mb_substr($value, -1) === '%') {
				$condition['operator'] = 'LIKE';
			}

			$query['condition'][] = $condition;
		}

		return $query;
	}
}