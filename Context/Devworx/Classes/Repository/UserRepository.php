<?php

namespace Devworx\Repository;

class UserRepository extends AbstractRepository {
	function __construct(){
		parent::__construct([
			'table' => 'user',
			'pk' => 'uid',
			'mapResult' => \Devworx\Model\User::class
		]);
	}
}