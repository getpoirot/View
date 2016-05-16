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
     * Call When Model Bind To Parent
     * 
     * @param iViewModel $parentView
     */
    function notifyRenderBy(iViewModel $parentView);

    /**
     * @param mixed      $result     Result of self::render
     * @param iViewModel $parentView
     */
    function afterRender($result, iViewModel $parentView);
}
