<?php

namespace Cascade\Cache;

use \Devworx\Devworx;
use \Devworx\Utility\FileUtility;

class CascadeCache extends \Devworx\Cache\AbstractFileCache {
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context,false);
		
	}
	
	public function file(string $context,...$more): string {
		$context = ucfirst(empty($context) ? Devworx::context() : $context);
		if( empty($more) ) return '';
		
		[$renderContext] = $more;
		
		$file = $renderContext->getIdentifier();
		return "{$this->folder}/{$context}/{$file}.php";
	}
	
	public function create(string $context, ...$more): bool {
		if( empty($more) ) return false;
		$file = $this->file($context,...$more);
		if( empty($file) ) return false;
		
		[$renderContext,$template] = $more;
		if( file_exists( $file ) ) return true;
		
		$context = ucfirst( empty($context) ? Devworx::context() : $context );

		if( !is_dir("{$this->folder}/{$context}") )
			mkdir("{$this->folder}/{$context}",0777,true);
		
			
		//$compiled = $renderContext->parser->parse($template)->compile('$context','null');
				
		$code = '(fn()=>implode("",['.$template.']))()'; 
		return $this->set( $context, $code, ...$more	);
	}
	
	function flush(string $context,...$more): bool {
		$context = ucfirst($context);
		FileUtility::unlinkRecursive("{$this->folder}/{$context}");
		return true;
	}
}