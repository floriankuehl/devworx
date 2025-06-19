<?php

namespace Devworx\Cache;

use \Devworx\Context;
use \Devworx\Cache\AbstractVariableCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;

class ClassCache extends AbstractVariableCache {
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context);
	}
	
	function create(string $context,...$more): bool {
		if( empty($context) ){
			$result = true;
			foreach( Context::contexts() as $ctx )
				$result = $result && $this->create($ctx,...$more);
			return $result;
		}
		$folder = Context::folder();
		return $this->set($context,"{$folder}/{$context}/Classes");
	}
	
	
}