<?php
namespace Poirot\View\Interfaces\Respec;

use Poirot\View\Interfaces\iInterpreterModel;

interface iMInterpreterAware
{
    /**
     * Set Interpreter Engine
     *
     * @param iInterpreterModel $engine
     *
     * @return $this
     */
    function setInterpreter(iInterpreterModel $engine);
}
