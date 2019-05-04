<?php
namespace Poirot\View\ViewModel;

use Poirot\Std\Mixin;

use Poirot\View\Exception\exTemplateNotFound;
use Poirot\View\Interfaces\iViewRenderer;

/**
 * All isolated render area must extend this class
 *
 * why isolated?
 * - maybe you want View Renderer with different utility
 *   such as plugins helper, custom function helper, service manager aware
 *   and so ...
 *
 * [code]
 *    // inside included capture file, you can use
 *    $this->capture($headerInclude, ['title' => 'This is my site title']);
 * [/code]
 *
 */
class RendererPhp
    extends Mixin // allow to define basic view helpers as callback methods
    implements
    iViewRenderer
{
    protected $_curr__captureVars = null;

    /**
     * Capture Included File Into This Object
     *
     * !! use absolute file path
     *
     * @param string $templateFullPathname File Path To Include
     * @param array $__vars
     *
     * @throws \Exception
     * @return string|mixed if included file return something
     */
    function capture($templateFullPathname, array $__vars = array())
    {
        // TODO hierarchy variable pass to child not consumed successfully
        // ability to call capture method again within included file-
        // with parent/first call variables
        if ($this->_curr__captureVars === null)
            $this->_curr__captureVars = $__vars;
        else
            (!empty($__vars)) ?: $__vars = $this->_curr__captureVars;

        if (!file_exists($templateFullPathname)) {
            ## look for called script backtrace
            $backTrace = debug_backtrace()[0];
            if (isset($backTrace['file'])) {
                ## [dirname:/var/www/html/error/].error.page.php
                $tInclude = dirname($backTrace['file']).'/'.trim($templateFullPathname, '/');
                (!is_file($tInclude)) ?: $templateFullPathname = $tInclude;
                unset($backTrace);unset($tInclude);
            }
        }

        if(!is_file($templateFullPathname) || !is_readable($templateFullPathname))
            throw new exTemplateNotFound(sprintf('Cant include (%s).', $templateFullPathname));

        $this->__file_to_include = $templateFullPathname;
        unset($templateFullPathname);

        extract($__vars);
        unset($__vars);

        try {
            ob_start();
            if (1 === $result = include $this->__file_to_include)
                ## file included but return nothing
                $result = ob_get_clean();
            else
                ob_end_clean();
        }
        catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        unset($this->__file_to_include);
        return $result;
    }
}
