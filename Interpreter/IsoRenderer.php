<?php
namespace Poirot\View\Interpreter;

use Poirot\Core\Traits\OpenCallTrait;
use Poirot\View\Exception\TemplateNotFoundException;

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
    use OpenCallTrait;

    protected $_curr__captureVars = null;

    /**
     * Capture Included File Into This Object
     *
     * !! use absolute file path
     *
     * @param string $absFilePath File Path To Include
     * @param array $__vars
     *
     * @throws \Exception
     * @return string
     */
    function capture($absFilePath, array $__vars = [])
    {
        // ability to call capture method again within included file-
        // with parent/first call variables
        if ($this->_curr__captureVars === null)
            $this->_curr__captureVars = $__vars;
        else
            (!empty($__vars)) ?: $__vars = $this->_curr__captureVars;

        if (!file_exists($absFilePath)) {
            ## look for called script backtrace
            $backTrace = debug_backtrace()[0];
            if (isset($backTrace['file'])) {
                ## [dirname:/var/www/html/error/]general.php
                $tInclude = dirname($backTrace['file']).'/'.trim($absFilePath, '/');
                (!is_file($tInclude)) ?: $absFilePath = $tInclude;
                unset($backTrace);unset($tInclude);
            }
        }

        if(!is_file($absFilePath) || !is_readable($absFilePath))
            throw new TemplateNotFoundException(sprintf('Cant include (%s).', $absFilePath));

        $this->absFilePath = $absFilePath;
        unset($absFilePath);

        extract($__vars);
        unset($__vars);

        try {
            ob_start();
            include $this->absFilePath;
            $result = ob_get_clean();
        }
        catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        unset($this->absFilePath);

        return $result;
    }
}
