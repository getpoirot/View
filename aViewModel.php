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
    /** @var CollectionPriority (DecorateViewModelFeatures) */
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
        $this->queue = new CollectionPriority;
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
         #
         $makeCopyOfCurrentQueue = clone $this->queue;
         foreach($this->queue as $vc)
         {
             /** @var DecorateViewModelFeatures $vc */

             try
             {
                 $vc->delegateRenderBy($this);
                 // prepare bind view model result into parent model
                 $vc->assertRenderResult(
                     $vc_render = $vc->render()
                     , $this
                 );

                 if ( $vc->isFinal() )
                     $rFinal = (string) $vc_render;
             }
             catch (\Exception $e) {
                 ## set render flag to false, render job is done
                 $this->_c__isNowRendering = false;
                 throw $e;
             }
         }
         
         $this->queue = $makeCopyOfCurrentQueue;
         $this->_c__isNowRendering = false;


         if ( isset($rFinal) )
             return $rFinal;

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
    function bind(iViewModelBindAware $viewModel, $priority = 0)
    {
        $this->queue->insert( (object) $viewModel );
        return $this;
    }
    
    
    // ..

    function __clone()
    {
        $_f__clone_array = function($arr) use (&$_f__clone_array) {
            foreach ($arr as &$v) {
                if (is_array($v))
                    $_f__clone_array($v);
                elseif (is_object($v))
                    $v = clone $v;
            }
        };

        foreach($this as &$val) {
            if (is_array($val))
                $_f__clone_array($val);
            elseif (is_object($val))
                $val = clone $val;
        }
    }
}
