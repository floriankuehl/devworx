<?php

namespace Devworx\Cache;

use \Devworx\Devworx;
use \Devworx\Cache\AbstractCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;

final class HtaccessCache extends AbstractFileCache {
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context,false);
		//$this->folder = PathUtility::public();
	}
	
	function file(string $context,...$more):string {
		//return $this->folder . '/.htaccess';
		$context = $context === '' ? 'context' : strtolower($context);
		return "{$this->folder}/{$context}.htaccess";
	}
	
	function addBlock(string $label,string $content): string {
		return "# {$label}\n{$content}";
	}
	
	function addFile(string $label,string $file): string {
		return $this->addBlock($label,FileUtility::get($file));
	}
	
	function checkAdd(string $context,string $label,string $file, array &$list){
		$path = PathUtility::configuration($context,$file);
		if( file_exists($path) )
			$list []= $this->addFile($label,$path);
	}
	
	function create(string $context,...$more): bool {
		$file = $this->file($context,...$more);
		
		if( file_exists( $file ) ) return true;

		$framework = Devworx::framework();

		$content = [];
		$this->checkAdd(
			$framework,
			'Global',
			'Global.htaccess',
			$content
		);
		
		foreach( Devworx::contexts() as $ctx ){
			if( $ctx === $framework ) 
				continue;
			$this->checkAdd(
				$ctx,
				$ctx,
				'Context.htaccess',
				$content
			);
		}
		
		$this->checkAdd(
			$framework,
			$framework,
			'Context.htaccess',
			$content
		);
		$content = implode(PHP_EOL.PHP_EOL,$content);
		
		return $this->set($context,$content);
	}
}