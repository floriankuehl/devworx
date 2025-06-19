<?php

namespace Devworx;

use \Devworx\Cache\PerformanceCache;
use \Devworx\Utility\FileUtility;

class Performance {
	
	static $cache = null;
	
	static function start(...$contexts): bool {
		return array_reduce(
			$contexts,
			fn($acc,$context)=>$acc && self::$cache->start($context),
			true
		);
	}
	
	static function stop(...$contexts): bool {
		return array_reduce(
			$contexts,
			fn($acc,$context)=>$acc && self::$cache->stop($context),
			true
		);
	}
	
	static function initialize(string $context=''):bool {
		self::$cache = new PerformanceCache('Performance',$context);
		return self::start($context);
	}
	
	static function dump(string $fileName): bool {
		
		$dump = [];
		$total = 0;
		foreach( self::$cache->all() as $context => $list ){
			$spans = array_map(
				fn($row) => isset($row[1]) ? $row[1] - $row[0] : 0,
				$list
			);
			$contextTotal = array_reduce($spans,fn($acc,$span)=>$acc + $span,0);
			
			$dump[$context] = [
				'list' => $list,
				'spans' => $spans,
				'total' => $contextTotal
			];
			$total += $contextTotal;
		}
		
		return FileUtility::setJson($fileName,[
			'data' => $dump,
			'total' => $total,
		]);
	}
	
}