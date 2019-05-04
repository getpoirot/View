<?php
namespace Poirot\View;

use Poirot\Std\Struct\CollectionPriority;
use Poirot\View\Interfaces\iViewModel;

/*
// put viewChild render content into $content variable of viewTemplate
// in parent layout it can be printed, exp. <?= $content ?>
$viewAsTemplate->bind( new DecorateViewModelFeatures(
    $viewModel
    , function(){}
    , function($resultRender, $parent) {
        $parent->variables()->set('content', (string) $resultRender);
    }
));
*/


/*
return function ($parent) use ($var)
{
    $parent->setFinal();
};
*/

/*
return function ($parent) use ($var)
{
    /** @var \Poirot\View\ViewModelTemplate $parent * /
    $parent->root()->setVariables([
        'header_class' => ['fixed', 'fullwidth', 'dashboard'],
        'no_footer'    => true,
    ]);
};
*/

/**
 * Decorator Allow ViewModels act as iViewModelBindAware
 * with ability to bind to another viewModel
 *
 */
class DecorateViewModel
    implements
    iViewModel
{
    /** @var iViewModel */
    protected $_view;

    /** @var CollectionPriority (DecorateViewModelFeatures) */
    protected $queue;
    /** @var callable(iViewModel $parent, $self = null) */
    protected $delegateRenderBy;
    /** @var callable($renderResult, $parent, $self = null) */
    protected $assertRenderResult;
    /** @var null|DecorateViewModel Root if it has! */
    protected $myRoot;
    protected $nextRoot;

    protected $_c__isNowRendering = false;


    /**
     * ViewModelDecorateFeatures constructor.
     *
     * @param iViewModel $viewModel
     * @param callable   $delegateRenderBy   f($parentView, $self)
     * @param callable   $assertRenderResult f($result, $parent, $self)
     */
    function __construct(iViewModel $viewModel, $delegateRenderBy = null, $assertRenderResult = null)
    {
        $this->_view = $viewModel;
        if ($viewModel instanceof DecorateViewModel && $viewModel->parent() === null)
            // Change parent of chain to this if undefined!!
            $viewModel->myRoot = $this;

        $this->delegateRenderBy   = $delegateRenderBy;
        $this->assertRenderResult = $assertRenderResult;

        $this->queue = new CollectionPriority;
    }

    static function of(iViewModel $viewModel, $delegateRenderBy = null, $assertRenderResult = null)
    {
        return new static($viewModel, $delegateRenderBy, $assertRenderResult);
    }

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
            /** @var DecorateViewModel $vc */

            try
            {
                $vc->delegateRenderBy($this, $this->nextRoot);
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
        return $this->_view->render();
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
     * @param DecorateViewModel $viewModel
     * @param int               $priority
     *
     * @return $this
     */
    function bind(DecorateViewModel $viewModel, $priority = 0)
    {
        $viewModel->myRoot = $this;

        $this->queue->insert( (object) $viewModel, $priority );
        return $this;
    }

    /**
     * Call Before Rendering ViewModel Itself,
     * tell viewModel About ParentView that render it.
     *
     * @param DecorateViewModel $parentView
     * @param null|iViewModel   $rootPassedToNext
     *
     * @return void
     */
    function delegateRenderBy(DecorateViewModel $parentView, $rootPassedToNext = null)
    {
        if ($rootPassedToNext === null)
            // first segment of list do not pass nextRoot
            $rootPassedToNext = $parentView;

        $this->myRoot   = $rootPassedToNext;
        $this->nextRoot = $parentView;


        if (null ===  $callback = $this->delegateRenderBy )
            return;

        // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
        if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0) {
            $callback = \Closure::bind(
                $callback
                , $this->_view
                , get_class($this)
            );
        }

        call_user_func($callback, $parentView, $parentView->parent(), $this);
    }

    /**
     * @param mixed             $result Result of self::render
     * @param DecorateViewModel $parent
     */
    function assertRenderResult($result, DecorateViewModel $parent)
    {
        if (null === $callback = $this->assertRenderResult)
            return;

        // DO_LEAST_PHPVER_SUPPORT 5.4 closure bindto
        if ($callback instanceof \Closure && version_compare(phpversion(), '5.4.0') > 0) {
            $callback = \Closure::bind(
                $callback
                , $this->_view
                , get_class($this)
            );
        }

        call_user_func($callback, $result, $parent, $parent->myRoot, $this);
    }

    /**
     * Proxy all calls to wrapped ViewModel
     * @return mixed
     */
    function __call($method, $arguments)
    {
        $r = call_user_func_array(array($this->_view, $method), $arguments);
        if ($r === $this->_view)
            // return instance of self if injected return itself
            return $this;

        return $r;
    }

    /**
     * Parent
     *
     * @return null|DecorateViewModel
     */
    function parent()
    {
        return $this->myRoot;
    }

    /**
     * Root
     *
     * @return null|DecorateViewModel
     */
    function root()
    {
        // change template to login layout
        $root = $this;
        while( $next = $root->parent() )
            $root = $next;

        return ($root === $this) ? null : $root;
    }

    // Wrapper:
    
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
