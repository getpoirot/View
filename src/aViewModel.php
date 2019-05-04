<?php
namespace Poirot\View;

use Poirot\Std\ConfigurableSetter;

use Poirot\View\Interfaces\iViewModel;


abstract class aViewModel
    extends ConfigurableSetter
    implements iViewModel
{
    /** @var bool Is Final ViewModel */
    protected $isFinal;


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
