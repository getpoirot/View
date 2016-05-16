<?php
namespace Poirot\View;

use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Struct\CollectionPriority;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\ViewModel\Feature\iViewModelBindAware;

abstract class aViewModel
    extends ConfigurableSetter
    implements iViewModel
{
    /** @var bool Is Final ViewModel */
    protected $isFinal;

    /** @var CollectionPriority */
    protected $queue;

    protected $_c__isNowRendering = false;

    /**
     * Construct
     *
     * @param array|\Traversable $options
     */
    function __construct($options = null)
    {
        parent::__construct($options);
        $this->queue = new CollectionPriority();
    }

    /**
     * Render View Model
     *
     * @return string
     * @throws \Exception
     */
    abstract function doRender();
    
    /**
     * Render View Model
     *
     * - render bind view models first
     *
     * @return string
     * @throws \Exception
     */
     final function render()
     {
         if ($this->_c__isNowRendering)
             return '';

         $this->_c__isNowRendering = true;
         
         # Render Bind View Models:
         $curQueue = clone $this->queue;
         /** @var \stdClass {model:, callback:} $vc */
         foreach($this->queue as $vc) 
         {
             // drop it out
             // $this->queue->del($vc);

             $callback   = $vc->callback;
             // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
             if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0)
                $callback   = $callback->bindTo($this);

             $viewModel = new ViewModelDecorateFeatures($vc->model);
             ($callback === null) ?: $viewModel->afterRender = $callback;
             try 
             {
                 if ($viewModel instanceof iViewModelBindAware) 
                     $viewModel->notifyRenderBy($this);

                 // prepare bind view model result into parent model
                 $vResult = $viewModel->render();
                 if ($viewModel instanceof iViewModelBindAware)
                     $viewModel->afterRender($vResult, $this);
             }
             catch (\Exception $e) {
                 ## set render flag to false, render job is done
                 $this->_c__isNowRendering = false;
                 throw $e;
             }
         }
         
         $this->queue = $curQueue;
         $this->_c__isNowRendering = false;

         # Then Render Self ...
         ## ... implement on extend classes
         return $this->doRender();
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
        $this->isFinal = (boolean) $flag;
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
     * !! Final ViewModels cant bind
     *
     * $closure:
     *   prepare bind view model result into parent model (after render).
     *   function($renderResult, $parentModel) {
     *      ## $renderResult is render result of
     *      #- bind viewModel as string.
     *      #- $this == $parentModel == static extended class
     *   }
     *
     * @param iViewModel $viewModel
     * @param \Closure   $closure
     *
     * @return $this
     */
    function bind(iViewModel $viewModel, \Closure $closure = null)
    {
        $viewModelStore = array('model' => $viewModel, 'callback' => $closure);
        $this->queue->insert( (object) $viewModelStore );
        return $this;
    }
}
