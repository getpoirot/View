<?php
namespace Poirot\View\ViewModel\Feature;

use Poirot\View\Interfaces\iViewModel;

/**
 * Classes that extend this Feature 
 * aware of Bind Behaviours.
 * 
 */
interface iViewModelBindAware
{
    /**
     * Call Before Rendering ViewModel Itself,
     * tell viewModel About ParentView that render it.
     *
     * @param iViewModel $parentView
     */
    function delegateRenderBy(iViewModel $parentView);

    /**
     * @param mixed $result Result of self::render
     * @param iViewModel $parent
     */
    function assertRenderResult($result, iViewModel $parent);
}
