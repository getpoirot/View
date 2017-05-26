<?php
namespace Poirot\View;

use Poirot\View\Interfaces\iViewModel;
use Poirot\View\ViewModel\Feature\iViewModelBindAware;


/**
 * Decorator Allow ViewModels act as iViewModelBindAware
 * with ability to bind to another viewModel
 *
 */
class DecorateViewModelFeatures
    implements
    iViewModel
    , iViewModelBindAware
{
    /** @var iViewModel */
    protected $_view;

    /** @var callable(iViewModel $parent, $self = null) */
    protected $delegateRenderBy;
    /** @var callable($renderResult, $parent, $self = null) */
    protected $assertRenderResult;


    /**
     * ViewModelDecorateFeatures constructor.
     *
     * @param iViewModel $viewModel
     * @param callable   $delegateRenderBy   f($parentView, $self)
     * @param callable   $assertRenderResult f($result, $parent, $self)
     */
    function __construct(iViewModel $viewModel, $delegateRenderBy, $assertRenderResult)
    {
        $this->_view = $viewModel;

        if (! (is_callable($delegateRenderBy) && is_callable($assertRenderResult)) )
            throw new \InvalidArgumentException('Delegate and Assert must be a valid callable.');

        $this->delegateRenderBy   = $delegateRenderBy;
        $this->assertRenderResult = $assertRenderResult;
    }

    /**
     * Proxy all calls to wrapped ViewModel
     * @return mixed
     */
    function __call($method, $arguments)
    {
        return call_user_func_array(array($this->_view, $method), $arguments);
    }

    /**
     * Call Before Rendering ViewModel Itself,
     * tell viewModel About ParentView that render it.
     *
     * @param iViewModel $parentView
     */
    function delegateRenderBy(iViewModel $parentView)
    {
        $callback = $this->delegateRenderBy;

        // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
        if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0) {
            $callback = \Closure::bind(
                $callback
                , $this
                , get_class($this)
            );
        }

        call_user_func($callback, $parentView, $this->_view);
    }

    /**
     * @param mixed $result Result of self::render
     * @param iViewModel $parent
     */
    function assertRenderResult($result, iViewModel $parent)
    {
        $callback = $this->assertRenderResult;

        // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
        if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0) {
            $callback = \Closure::bind(
                $callback
                , $this
                , get_class($this)
            );
        }

        call_user_func($callback, $result, $parent, $this->_view);
    }

    
    // Wrapper:
    
    /**
     * Render View Model
     *
     * - render bind view models first
     *
     * @return string
     */
    function render()
    {
        return $this->_view->render();
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
        $this->_view->setFinal($flag);
        return $this;
    }

    /**
     * Is Final View Model?
     *
     * @return bool
     */
    function isFinal()
    {
        return $this->_view->isFinal();
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
        $this->_view->bind($viewModel, $priority);
        return $this;
    }


    // Configurable

    /**
     * Build Object With Provided Options
     *
     * @param array $options        Associated Array
     * @param bool  $throwException Throw Exception On Wrong Option
     *
     * @return $this
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = false)
    {
        $this->_view->with($options, $throwException);
        return $this;
    }

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     * !! With this The classes that extend this have to
     *    implement desired parse methods
     *
     * @param array|mixed $optionsResource
     * @param array $_
     *        usually pass as argument into ::with if self instanced
     *
     * @throws \Exception if resource not supported
     * @return array
     */
    static function parseWith($optionsResource, array $_ = null)
    {
        throw new \Exception('Not Implemented.');
    }

    /**
     * Is Configurable With Given Resource
     *
     * @param mixed $optionsResource
     * @throws \Exception
     * @return bool
     */
    static function isConfigurableWith($optionsResource)
    {
        throw new \Exception('Not Implemented.');
    }
}
