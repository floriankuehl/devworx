<?php

namespace Devworx\Utility;

use Devworx\Frontend;

/**
 * Helper Class for easy page manipulation
 * Reads and writes the page configuration
 * For use in action-Context
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
	 * @param string $value
	 * @return void
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
	
	/**
	 * Sets the page icon
	 * @param string $value
	 * @return void
	 */
	public static function setIcon(string $value): void {
		Frontend::setConfig($value,'head','favicon');
	}

	/**
	 * Gets the page icon
	 * @return string
	 */
	public static function getIcon(): string {
		return Frontend::getConfig('head','favicon');
	}
	
	/**
	 * Sets the page author
	 * @param string $value
	 * @return void
	 */
	public static function setAuthor(string $value): void {
		Frontend::setConfig($value,'head','meta','author');
	}

	/**
	 * Gets the page author
	 * @return string
	 */
	public static function getAuthor(): string {
		return Frontend::getConfig('head','meta','author');
	}
  
}