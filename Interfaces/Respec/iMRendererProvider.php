<?php
namespace Poirot\View\Interfaces\Respec;

use Poirot\View\Interpreter\IsoRenderer;

interface iMRendererProvider
{
    /**
     * Get Iso Renderer
     *
     * @return IsoRenderer
     */
    function renderer();
}
