<?php
namespace Poirot\View;

use Poirot\View\Interfaces\iViewModel;
use Poirot\View\ViewModel\Feature\iViewModelBindAware;

class ViewModelDecorateFeatures
    implements
    iViewModel
    , iViewModelBindAware
{
    /** @var iViewModel */
    protected $_view;

    /** @var callable(iViewModel $parent) */
    public $onNotifyRender;
    /** @var callable($renderResult, iViewModel $parent) */
    public $afterRender;

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
     * Call When Model Bind To Parent
     *
     * @param iViewModel $parentView
     */
    function notifyRenderBy(iViewModel $parentView)
    {
        $callback = null;
        if ($callback = $this->onNotifyRender) VOID;
        elseif (method_exists($this->_view, 'notifyRenderBy'))
            $callback = array($this->_view, 'notifyRenderBy');

        if ($callback !== null) call_user_func($callback, $parentView);
    }

    /**
     * @param mixed $result Result of self::render
     * @param iViewModel $parentView
     */
    function afterRender($result, iViewModel $parentView)
    {
        $callback = null;
        if ($callback = $this->afterRender) VOID;
        elseif (method_exists($this->_view, 'afterRender'))
            $callback = array($this->_view, 'afterRender');

        if ($callback !== null) call_user_func($callback, $result, $parentView);
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
