<?php
namespace Poirot\View;

use Poirot\Std\Struct\CollectionPriority;
use Poirot\View\Interfaces\iViewModel;


/*
// Demonstrate Layout Decorator
//
$viewTemplate = new P\View\ViewModelTemplate;
$viewTemplate
    ->setRenderer( new P\View\ViewModel\RendererPhp )
    ->setTemplate('template.php')
    ->resolver(function($resolver) {
        $resolver->addResource('**', __DIR__.'/data/');
    })
    ->setVariables([
        'content' => 'default_content',
        'footer'  => 'this is footer',
    ])
;

$decorate = new P\View\DecorateViewModel(
    new P\View\ViewModelStatic(['content' => 'This is content of page'])
);

$decorate->onAfterViewModelRendered(function($result, $parent, $self) {
    $parent->view()->setVariables(['content' => $result]);
});

$viewTemplate = new P\View\DecorateViewModel($viewTemplate);
$viewTemplate->bind($decorate, 10, 'page_content');

echo $viewTemplate->render();
die;
*/


/*
// want to render PageView without Layout
//
$decorate = new P\View\DecorateViewModel(
    new P\View\ViewModelStatic(['content' => 'This is content of page'])
    , function($result, $parent, $self) {
        $self->setFinal();
    }
);
*/


/**
 * Add ability by wrap a ViewModel to bind to another viewModel
 *
 */
class DecorateViewModel
    implements iViewModel
{
    /** @var iViewModel */
    protected $viewWrap;

    /** @var CollectionPriority */
    protected $queue;
    /** @var null|DecorateViewModel Root if it has! */
    protected $parentDecorator;

    protected $onAfterViewModelRendered;
    protected $onBeforeViewModelRender;

    protected $_c__isNowRendering = false;
    protected $__mapped_items;


    /**
     * ViewModelDecorateFeatures constructor.
     *
     * @param iViewModel $viewModel
     */
    function __construct(iViewModel $viewModel)
    {
        $this->viewWrap = $viewModel;
        if ($viewModel instanceof DecorateViewModel && $viewModel->parent() === null)
            // Change parent of chain to this if undefined!!
            $viewModel->parentDecorator = $this;
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
        /** @var DecorateViewModel $bindView */
        foreach(clone $this->_priorityQueue() as $bindView)
        {
            try {
                if (false !== $bindView->_callBeforeViewModelRender($this) ) {
                    // prepare bind view model result into parent model
                    $bindView->_callAfterViewModelRendered(
                        $bindViewRender = $bindView->render()
                        , $this
                    );
                }
            } catch (\Exception $e) {
                ## set render flag to false, render job is done
                $this->_c__isNowRendering = false;
                throw $e;
            }


            if ( $bindView->isFinal() )
                $rFinal = (string) $bindViewRender;
        }

        $this->_c__isNowRendering = false;

        if ( isset($rFinal) )
            return $rFinal;


        return $this->view()->render();
    }

    /**
     * After View Model Rendered
     *
     * function(
     *    $result    // render result
     *  , $parent    // parent; decorator view who bind this view; binder
     *  , $this      // self; view that result belong to it
     * )
     *
     * @param callable $callable
     *
     * @return $this
     * @throws \Exception
     */
    function onAfterViewModelRendered($callable)
    {
        if (! is_callable($callable) )
            throw new \Exception('Callable Should Given.');


        $this->onAfterViewModelRendered = $callable;
        return $this;
    }

    /**
     * Before Render View Model
     *
     * function(
     *  , $parent    // parent; decorator view who bind this view; binder
     *  , $this      // self; view that result belong to it
     * )
     *
     * @param callable $callable
     *
     * @return $this
     * @throws \Exception
     */
    function onBeforeViewModelRender($callable)
    {
        if (! is_callable($callable) )
            throw new \Exception('Callable Should Given.');


        $this->onBeforeViewModelRender = $callable;
        return $this;
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
     * @param int|string        $priorityOrTag
     * @param string|null       $tag
     *
     * @return $this
     */
    function bind(DecorateViewModel $viewModel, $priorityOrTag = 10, $tag = null)
    {
        $priority = $priorityOrTag;
        if (is_string($priorityOrTag)) {
            $priority = 10; // default
            $tag = $priorityOrTag;
        }


        $viewModel->parentDecorator = $this;
        $this->_priorityQueue()->insert($viewModel, $priority);

        if ($tag !== null) {
            $this->__mapped_items[$this->_normalize($tag)] = [ // allow override current tags
                'priority' => $priority,
                'view'     => $viewModel
            ];
        }

        return $this;
    }

    /**
     * Get Bind Decorate View Model By Tag
     *
     * @param string $tag
     *
     * @return iViewModel|null
     */
    function getBindByTag($tag)
    {
        $tag = $this->_normalize($tag);

        if (! isset($this->__mapped_items[$tag]) )
            return null;

        return $this->__mapped_items[$tag]['view'];
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

    /**
     * Parent
     *
     * @return null|DecorateViewModel
     */
    function parent()
    {
        return $this->parentDecorator;
    }

    /**
     * Get Wrapped View Object
     *
     * @return iViewModel
     */
    function view()
    {
        return $this->viewWrap;
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
        $this->viewWrap->setFinal($flag);
        return $this;
    }

    /**
     * Is Final View Model?
     *
     * @return bool
     */
    function isFinal()
    {
        return $this->viewWrap->isFinal();
    }


    // ..

    /**
     * @param $parent
     */
    protected function _callBeforeViewModelRender($parent)
    {
        if (null === $callback = $this->onBeforeViewModelRender)
            return;

        call_user_func(
            $callback
            , $parent    // parent; decorator view who bind this view; binder
            , $this      // self; view that result belong to it
        );
    }

    /**
     * @param mixed $result Result of self::render
     * @param $parent
     */
    protected function _callAfterViewModelRendered($result, $parent)
    {
        if (null === $callback = $this->onAfterViewModelRendered)
            return;

        call_user_func(
            $callback
            , $result    // render result
            , $parent    // parent; decorator view who bind this view; binder
            , $this      // self; view that result belong to it
        );
    }

    /**
     * Proxy all calls to wrapped ViewModel
     * @inheritdoc
     *
     * @return mixed|$this
     */
    function __call($method, $arguments)
    {
        if (!$this->viewWrap instanceof DecorateViewModel && !method_exists($this->viewWrap, $method) )
            throw new \RuntimeException(sprintf(
                'Call to undefined method %s::%s()'
                , get_class($this->viewWrap)
                , $method
            ));

        $r = call_user_func_array([$this->viewWrap, $method], $arguments);
        if ($r === $this->viewWrap)
            // return instance of self if injected return itself
            return $this;

        return $r;
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
        $this->viewWrap->with($options, $throwException);
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
        throw new \Exception('Not Allowed.');
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
        throw new \Exception('Not Allowed.');
    }


    // ..

    /**
     * @return CollectionPriority
     */
    protected function _priorityQueue()
    {
        if (! $this->queue )
            $this->queue = new CollectionPriority;

        return $this->queue;
    }

    /**
     * Normalize Key
     *
     * @param string $key
     *
     * @return string
     */
    private function _normalize($key)
    {
        return strtolower($key);
    }


    function __clone()
    {
        $this->viewWrap = clone $this->viewWrap;
    }
}
