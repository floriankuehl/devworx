<?php

namespace Devworx\Utility;

use Devworx\Frontend;

/**
 * Helper Class for easy page manipulation
 * Reads and writes the page configuration
 */

class PageUtility {
	
	/**
	 * Sets the page title
	 * @param string $value
	 * @return void
	 */
	public static function setTitle(string $value): void {
		Frontend::setConfig($value,'head','title');
	}

	/**
	 * Gets the page title
	 * @return string
	 */
	public static function getTitle(): string {
		return Frontend::getConfig('head','title');
	}


	/**
	 * Sets the page charset
	 * @param string $value
	 * @return void
	 */
	public static function setCharset(string $value): void {
		Frontend::setConfig($value,'charset');
	}

	/**
	 * Gets the page charset
	 * @return string
	 */
	public static function getCharset(): string {
		return Frontend::getConfig('charset');
	}

	/**
	 * Sets the page doctype
	 * @return string
	 */
	public static function setDoctype(string $value): void {
		Frontend::setConfig($value,'doctype');
	}

	/**
	 * Gets the page doctype
	 * @return string
	 */
	public static function getDoctype(): string {
		return Frontend::getConfig('doctype');
	}
  
}