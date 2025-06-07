<?php

namespace Devworx\Interfaces;

/**
 * The walker interface
 */
interface IWalker {
	function Start(array &$list): void;
	function Step(array &$list,$index,&$row): void;
	function Walk(array &$list): void;
	function End(array &$list): void;
}