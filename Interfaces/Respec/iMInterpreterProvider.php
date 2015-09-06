<?php
namespace Poirot\View\Interfaces\Respec;

use Poirot\View\Interfaces\iInterpreterModel;

interface iMInterpreterProvider
{
    /**
     * Get Interpreter Engine
     *
     * @return iInterpreterModel
     */
    function interpreter();
}
