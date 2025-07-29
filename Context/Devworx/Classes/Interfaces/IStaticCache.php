<?php

namespace Devworx\Interfaces;


interface IStaticCache {
	
	static ICache $cache { get; set; }
	
	static function initialize(string $context=''):bool;
	static function get(string $context,...$more): mixed;
	static function set(string $context,...$more): bool;
	static function create(string $context,...$more): bool;
	static function flush(string $context,...$more): bool;
	static function needsUpdate(string $context,...$more): bool;
	static function all(string $context=''): \Traversable;
}