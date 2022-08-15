<?php

namespace Kanin\MyTools\MongoDB;

class WhereHandle {

	/**
	 * @param $opt
	 * @param $val
	 * @return array|false
	 */
	public function handle($opt, $val) {
		switch ($opt) {
			case '=':
				$filter = $val;
				break;
			case '<':
				$filter = ['$lt' => $val];
				break;
			case '<=':
				$filter = ['$lte' => $val];
				break;
			case '>':
				$filter = ['$gt' => $val];
				break;
			case '>=':
				$filter = ['$gte' => $val];
				break;
			case '<>':
			case '!=':
				$filter = ['$ne' => $val];
				break;
			case 'in':
				$filter = ['$in' => $val];
				break;
			case 'nin':
				$filter = ['$nin' => $val];
				break;
			default:
				return false;
		}
		return $filter;
	}
}