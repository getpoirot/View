<?php
namespace Poirot\View\Interfaces;

use Poirot\Std\Interfaces\Pact\ipConfigurable;
use Poirot\View\ViewModel\Feature\iViewModelBindAware;


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

    /**
     * Bind a ViewModel Into This
     *
     * !! Final ViewModels cant bind
     *
     * $closure:
     *   prepare bind view model result into parent model
     *   function($renderResult, $parentModel) {
     *      ## $renderResult is render result of
     *      #- bind viewModel as string.
     *      #- $this == $parentModel == static extended class
     *   }
     *
     * @param iViewModelBindAware $viewModel
     * @param int                 $priority
     *
     * @return $this
     */
    function bind(iViewModelBindAware $viewModel, $priority = 0);
}
