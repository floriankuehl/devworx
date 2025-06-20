<?php

namespace Devworx\Cache;

use \Devworx\Context;
use \Devworx\Cache\AbstractCache;
use \Devworx\Cache\AbstractVariableCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;

class SymlinkCache extends AbstractVariableCache {
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context);
	}
	
	function flush(string $context,...$more): bool {
		if( $this->has($context) ){
			$link = $this->get($context);
			if( file_exists($link['link']) )
				unlink($link['link']);
			return parent::flush($context);
		}
		return false;
	}
	
	private function symlinkTarget(string $context){
		return realpath(
			implode('/',[
				getcwd(),
				$GLOBALS['DEVWORX']['PATH']['ROOT'],
				Context::folder(),
				$context,
				'Resources',
				'Public'
			])
		);
	}
	
	private function symlinkFile(string $context){
		return implode('/',[
			getcwd(),
			'resources',
			strtolower($context)
		]);
	}
	
	function needsUpdate(string $context,...$more): bool {
		$link = $this->symlinkFile($context);
		return !( 
			is_link($link) && 
			( readlink($link) === $this->symlinkTarget($context) ) 
		);
	}
		
	function create(string $context,...$more): bool {
		$link = $this->symlinkFile($context);
		if( is_link( $link ) ) unlink($link);
		
		$contexts = Context::folder();
		$target = $this->symlinkTarget($context);	
		return symlink($target, $link) && 
			$this->set($context,[
				'link' => $link,
				'target' => $target
			]);	
	}
		
}