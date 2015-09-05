<?php
namespace Poirot\View;

use Poirot\Core\BuilderSetterTrait;
use Poirot\Core\SplPriorityQueue;
use Poirot\View\Interfaces\iViewModel;

abstract class AbstractViewModel implements iViewModel
{
    use BuilderSetterTrait;

    /** @var bool Is Final ViewModel */
    protected $isFinal;

    /** @var SplPriorityQueue */
    protected $queue;

    /**
     * Construct
     *
     * @param array $options
     */
    function __construct(array $options = [])
    {
        if (!empty($options))
            $this->setupFromArray($options, true);

        $this->queue = new SplPriorityQueue;
    }

    /**
     * Render View Model
     *
     * - render bind view models first
     *
     * @return string
     */
     function render()
     {
         $render = '';

         # Render Bind View Models To Self
         ## view models can access self and inject render
         ## result into object properties
         $curQueue = clone $this->queue;
         foreach($this->queue as $vc) {
             ## bind closure to this object
             $this->queue->remove($vc);

             $closure   = $vc[1];
             $closure   = $closure->bindTo($this);

             /** @var iViewModel $viewModel */
             $viewModel = $vc[0];
             $vResult   = $viewModel->render();
             $closure($vResult);
         }
         $this->queue = $curQueue;

         # Then Render Self ...
         ## ... implement on extend classes

         return $render;
     }

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
    function setFinal($flag = true)
    {
        $this->isFinal = $flag;

        return $this;
    }

    /**
     * Is Final View Model?
     *
     * @return bool
     */
    function isFinal()
    {
        return $this->isFinal;
    }

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
     * @param \Closure $closure
     *
     * @return $this
     */
    function bind(iViewModel $viewModel, \Closure $closure)
    {
        $this->queue->insert([$viewModel, $closure], 0);

        return $this;
    }
}
 