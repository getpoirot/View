<?php
namespace Poirot\View\Interfaces;

use Poirot\Core\Interfaces\iBuilderSetter;

interface iViewModel extends iBuilderSetter
{
    /**
     * Construct
     *
     * @param array $options
     */
    function __construct(array $options = []);

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
     * - Final ViewModels cant bind
     *
     * $closure
     * this closure will bind to this view model
     * function($renderResult) {
     *    # $renderResult is render result of
     *    # $viewModel that we want inject into
     *    # $this viewModel with this closure
     * }
     *
     * @param iViewModel $viewModel
     * @param \Closure   $closure
     *
     * @return $this
     */
    function bind(iViewModel $viewModel, \Closure $closure);
}
