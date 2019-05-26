<?php
namespace Poirot\View\ViewModel;

/*
return new \Poirot\View\ViewModel\TwoStepViewCallable(function($parent, $self) {
    $variables = $self->variables();
    $content   = $variables->get('content');

    $content   = "<h3>$content</h3>";
    $parent->setVariables([
        'content' => $content
    ]);
});
*/

class TwoStepViewCallable
{
    protected $callable;


    /**
     * TwoStepViewCallable
     *
     * function(
     *    $parent  // parent; decorator view who bind this view; binder
     *    , $this  // self; view that result belong to it
     * )
     *
     * @param callable $callable
     *
     */
    function __construct($callable)
    {
        $this->callable = $callable;
    }


    function __invoke($parent, $self)
    {
        return call_user_func($this->callable, $parent, $self);
    }
}
