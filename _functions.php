<?php
namespace Poirot\View 
{
    /**
     * Renderer used by Two Step Views
     * 
     * - include template and retrieve defined variables
     * 
     * @return \Closure
     */
    function renderManipulatedVars()
    {
        return function($_l__template, $__vars) {
            if (!file_exists($_l__template))
                ## the two step is optional and can be avoided.
                return null;

            # render preLayer
            extract($__vars);
            unset($__vars);

            try {
                ob_start();
                include $_l__template;
                ob_get_clean();
            } catch (\Exception $e) {
                ob_end_clean();
                throw $e;
            }

            # set manipulated and new defined variables again
            $vars = get_defined_vars();
            unset($vars['_l__template']);

            return $vars;
        };
    }
}