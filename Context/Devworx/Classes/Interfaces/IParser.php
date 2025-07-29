<?php

namespace Devworx\Interfaces;


interface IParser {
	function parse(string $template): mixed;
	function compile( mixed $parsed, string $contextVar ): string;
	function parseCompile( string $template, string $contextVar ): string;
}