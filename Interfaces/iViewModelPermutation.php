<?php
namespace Poirot\View\Interfaces;

use Poirot\Std\Interfaces\Struct\iDataEntity;


interface iViewModelPermutation
    extends iViewModel
{
    /**
     * Set Variables
     *
     * - Variables can include null value
     *
     * @param array|\Traversable $vars
     *
     * @return $this
     */
    function setVariables($vars);

    /**
     * Variables
     *
     * @return iDataEntity
     */
    function variables();
}
