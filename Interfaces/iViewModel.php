<?php
namespace Poirot\View\Interfaces;

use Poirot\Std\Interfaces\Pact\ipConfigurable;


interface iViewModel
    extends ipConfigurable
{
    /**
     * Render View Model
     *
     * - render bind view models first
     *
     * @return string
     */
    function render();

    /**
     * Set This View As Final Model
     *
     * ! the final models can't nest to other
     *   models, but can have nested models
     *
     * @param bool $flag
     *
     * @return $this
     */
    function setFinal($flag = true);

    /**
     * Is Final View Model?
     *
     * @return bool
     */
    function isFinal();
}
