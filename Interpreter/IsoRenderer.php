<?php
namespace Poirot\View\Interpreter;

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
class IsoRenderer 
{
    /**
     * Capture Included File Into This Object
     *
     * @param string $include File Path To Include
     * @param array $vars
     *
     * @throws \Exception
     * @return string
     */
    function capture($include, array $vars = [])
    {
        if (!file_exists($include)) {
            ## look for called script backtrace
            $backTrace = debug_backtrace()[0];
            if (isset($backTrace['file']))
                ## [dirname:/var/www/html/error/]general.php
                $include = dirname($backTrace['file']).'/'.trim($include, '/');
        }

        if(!is_readable($include))
            throw new \Exception(sprintf('Cant include (%s).', $include));

        extract($vars);

        try {
            ob_start();
            include $include;
            $result = ob_get_clean();
        }
        catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return $result;
    }
}
