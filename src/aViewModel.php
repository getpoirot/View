<?php
namespace Poirot\View;

use Poirot\Std\ConfigurableSetter;

use Poirot\Std\Traits\tClone;
use Poirot\View\Interfaces\iViewModel;


abstract class aViewModel
    extends ConfigurableSetter
    implements iViewModel
{
    use tClone;

    /** @var bool Is Final ViewModel */
    protected $isFinal;


    /**
     * Render View Model
     *
     * - render bind view models first
     *
     * @return string
     */
    abstract function render();

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
}
