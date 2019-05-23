<?php
namespace Poirot\View\Interfaces;


interface iViewRenderer
{
    /**
     * Capture Included File Into This Object
     *
     * !! use absolute file path
     *
     * @param string $templateFullPathname File Path To Include
     * @param array  $__vars
     *
     * @throws \Exception
     * @return string
     */
    function capture($templateFullPathname, array $__vars = []);
}
