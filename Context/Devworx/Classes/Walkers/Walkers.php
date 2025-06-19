<?php

namespace Devworx\Walkers;

/** 
 * This class can handle multiple Walker- and SubsetWalker instances for one list
 */
class Walkers {
  
	/** 
	 * Performs all Start hooks of the extenders array on the list
	 *
	 * @param array $extenders The list of Walkers
	 * @param array $list The target list
	 * @return void
	 */
	public static function Start(array $extenders, array &$list): void{
		foreach( $extenders as $i => $extender )
			$extender->Start($list);
	}

	/** 
	 * Performs all End hooks of the extenders array on the list
	 *
	 * @param array $extenders The list of Walkers
	 * @param array $list The target list
	 * @return void
	 */
	public static function End(array $extenders, array &$list): void{
		foreach( $extenders as $i => $extender )
			$extender->End($list);
	}

	/** 
	 * Performs all Walk cycles of the extenders array on the list
	 *
	 * @param array $extenders The list of Walkers
	 * @param array $list The target list
	 * @return void
	 */
	public static function Apply(array $extenders, array &$list): void{
		foreach( $extenders as $j => $extender )
			$extender->Walk($list);
	}

	/** 
	 * Performs all Walk cycles of the sublist extenders array on the list
	 *
	 * @param array $extenders The list of SubsetWalkers
	 * @param array $list The target list
	 * @param string $key The sublist key
	 * @return void
	 */
	public static function ApplySub(array $extenders,array &$list,string $key): void{
		foreach( $list as $i => $row ){
			if( array_key_exists($key,$row) ){
				$sub = $row[$key];
				self::Apply($extenders,$sub);
				$list[$i][$key] = $sub;
			}
		}
	}

}
