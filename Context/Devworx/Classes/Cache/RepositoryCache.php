<?php

namespace Devworx\Cache;

use \Devworx\Cache\AbstractFileCache;

use \Devworx\Utility\ArrayUtility;
use \Devworx\Context;
use \Devworx\Database;

final class RepositoryCache extends AbstractFileCache {
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context,true);
		$this->list = Database::tables();
	}
	
	function file(string $context,...$more): string {
		$context = strtolower( $context === '' ? Context::get() : $context );
		$table = strtolower($more[0] ?? $this->id());
		return "{$this->folder}/{$table}.{$context}.json";
	}
	
	function create(string $context,...$more): bool {
		$file = $this->file($context,...$more);
		if( file_exists( $file ) || empty($more) )
			return true;
		
		return $this->set(
			$context,
			Database::repository($more[0],$context),
			$more[0]
		);
	}
		
}