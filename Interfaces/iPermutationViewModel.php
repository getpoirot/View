<?php
namespace Poirot\View\Interfaces;

use Poirot\Core\Entity;

interface iPermutationViewModel extends iViewModel
{
    /**
     * Set Variables
     *
     * @param array|Entity $vars
     *
     * @return $this
     */
    function setVariables($vars);

    /**
     * Variables
     *
     * @return Entity
     */
    function variables();
}
