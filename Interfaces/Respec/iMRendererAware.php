<?php
namespace Poirot\View\Interfaces\Respec;

use Poirot\View\Interpreter\IsoRenderer;

interface iMRendererAware
{
    /**
     * Set Iso Renderer
     *
     * Closure Renderer:
     * - function($template, $vars)
     * - closure bind to Self Interpreter Object
     *
     * @param IsoRenderer|\Closure $renderer
     *
     * @return $this
     */
    function setRenderer($renderer);
}
