<?php

namespace Devworx\Interfaces;

interface IRenderer
{
    /**
     * Returns a rendered representation of the given template
     *
     * @param mixed $template Template-Definition (String, Array, Objekt etc.)
     * @param array $variables Context variables
	 * @param string $renderContext the name of the render context
	 * @param string $encoding The encoding for the renderer
     * @return mixed Rendered representation
     */
    public function render(mixed $template, array $variables, string $renderContext, string $encoding): mixed;


	/**
     * Sets internal options for the renderer
     *
     * @param array $options Internal options
     * @return void
     */
    public function setOptions(array $options): void;
	
	/**
     * Checks if the given template is supported
     *
     * @param mixed $template Given template (string, object, array etc)
     * @return bool
     */
	public function supports(mixed $template): bool;
}
