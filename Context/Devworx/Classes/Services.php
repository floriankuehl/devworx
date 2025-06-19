<?php

namespace Devworx;

class Services
{
	/**
	 * contains all factory definitions
	 * @var array $definitions
	 **/
    protected static array $definitions = [];
	
	/**
	 * contains all singleton instances
	 * @var array $singletons
	 **/
    protected static array $singletons = [];

	/**
	 * gets all ids, optional with scope
	 *
	 * @param string $scope an optional scope
	 * @return array $ids a list of all registered ids
	 **/
	public static function ids(string $scope=null): array {
		if( $scope === null || $scope === '' )
			return array_keys(self::$definitions);
		$result = [];
		forEach(self::$definitions as $id => $definition){
			if( $scope === $definition['scope'] )
				$result[] = $id;
		}
        return $result;
	}
	
	/**
	 * checks for a given id in definitions
	 *
	 * @param string $id the given ID
	 * @return bool $result if true, id is found
	 **/
    public static function has(string $id): bool
    {
        return isset(self::$definitions[$id]);
    }
	
	/**
	 * sets a given definition
	 *
	 * @param string $id the system-wide id
	 * @param callable $factory the factory function
	 * @param string $scope the system-wide scope
	 * @param bool $singleton defines if the result of the factory is a singleton
	 * @return void
	 **/
    public static function set(string $id, callable $factory, string $scope='', bool $singleton = true): void
    {
        self::$definitions[$id] = [
            'factory' => $factory,
            'singleton' => $singleton,
			'scope' => $scope
        ];
    }
	
	/**
	 * sets all given definitions
	 *
	 * @param array $definitions the key value pair of id and factory
	 * @return void
	 **/
	public static function setAll( array $definitions ): void {
		foreach( $definitions as $id => $value ){
			self::set($id,$value);
		}
	}

	/**
	 * gets a given definition by id
	 *
	 * @param string $id the instance or definition id
	 * @return mixed $result returns the instance or the definition
	 **/
    public static function get(string $id): mixed
    {
        if (isset(self::$singletons[$id])) {
            return self::$singletons[$id];
        }

        if ( self::has($id) === false ){
            throw new \Exception("Service '$id' not found in container.");
        }

        $definition = self::$definitions[$id];
        $instance = ($definition['factory'])();

        if ($definition['singleton']) {
            self::$singletons[$id] = $instance;
        }

        return $instance;
    }
	
	/**
	 * gets instances by scope
	 *
	 * @param string $scope the instance or definition id
	 * @return array $result the list of instances of the scope
	 **/
    public static function getScope(string $scope,bool $assoc=true): array
    {
		return $assoc ? 
			array_reduce(
				self::ids($scope),
				function($acc,$id){ 
					$acc[$id] = self::get($id); 
					return $acc; 
				},
				[]
			) :
			array_map(
				fn($id) => self::get($id),
				self::ids($scope)
			);
    }
	
	/**
	 * resets the ServiceContainer, optional removes by scope
	 *
	 * @return void
	 **/
    public static function reset(string $scope=null): void
    {
		if( $scope === null || $scope === '' ){
			self::$definitions = [];
			self::$singletons = [];
			return;
		}
		
		foreach( self::ids($scope) as $id ){
			unset(self::$definitions[$id]);
			unset(self::$singletons[$id]);
		}
    }
}
