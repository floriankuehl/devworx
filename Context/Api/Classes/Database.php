<?php

namespace Api;

use \PDO;

class Database extends \Devworx\Database {
	/**
	 * Returns the PDO options
	 *
	 * @return array
	 */
	public static function options(): array {
		$options = parent::options();
		$options[PDO::ATTR_PERSISTENT] = true;
		return $options;
	}
}