<?php
namespace Poirot\View\Interfaces\Respec;

interface iMTemplateProvider 
{
    /**
     * Get Template
     *
     * - default, not set value is always empty
     *
     * @return mixed
     */
    function getTemplate();
}
