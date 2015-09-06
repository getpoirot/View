<?php
namespace Poirot\View\Interfaces\Respec;

interface iMTemplateAware 
{
    /**
     * Set Template
     *
     * - template must supported with engine
     *
     * @param mixed $template
     *
     * @return $this
     */
    function setTemplate($template);
}
