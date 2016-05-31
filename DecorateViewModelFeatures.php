<?php
namespace Poirot\View;

use Poirot\View\Interfaces\iViewModel;
use Poirot\View\ViewModel\Feature\iViewModelBindAware;

class DecorateViewModelFeatures
    implements
    iViewModel
    , iViewModelBindAware
{
    /** @var iViewModel */
    protected $_view;

    /** @var callable(iViewModel $parent, $self = null) */
    public $delegateRenderBy;
    /** @var callable($renderResult, $parent) */
    public $assertRenderResult;

    /**
     * ViewModelDecorateFeatures constructor.
     * @param iViewModel $viewModel
     */
    function __construct(iViewModel $viewModel)
    {
        $this->_view = $viewModel;
    }

    /**
     * Proxy all calls to wrapped ViewModel
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_view, $name), $arguments);
    }

    /**
     * Notify this class as bind view when render with parent
     *
     * @param iViewModel $parentView
     */
    function delegateRenderBy(iViewModel $parentView)
    {
        $callback = null;
        if ($callback = $this->delegateRenderBy) VOID;
        elseif (method_exists($this->_view, 'notifyRenderBy'))
            $callback = array($this->_view, 'notifyRenderBy');

        // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
        if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0) {
            $callback = \Closure::bind(
                $callback
                , $this
                , get_class($this)
            );
        }

        if ($callback !== null) call_user_func($callback, $parentView, $this);
    }

    /**
     * @param mixed $result Result of self::render
     * @param iViewModel $parent
     */
    function assertRenderResult($result, iViewModel $parent)
    {
        $callback = null;
        if ($callback = $this->assertRenderResult) VOID;
        elseif (method_exists($this->_view, 'afterRender'))
            $callback = array($this->_view, 'afterRender');

        // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
        if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0) {
            $callback = \Closure::bind(
                $callback
                , $this
                , get_class($this)
            );
        }

        if ($callback !== null) call_user_func($callback, $result, $parent, $this);
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
     * @param iViewModel $viewModel
     * @param \Closure $closure
     *
     * @return $this
     */
    function bind(iViewModel $viewModel, \Closure $closure = null)
    {
        $this->_view->bind($viewModel, $closure);
        return $this;
    }

    /**
     * Build Object With Provided Options
     *
     * @param array|\Traversable $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @return $this
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with($options, $throwException = false)
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
