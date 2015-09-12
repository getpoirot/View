<?php
namespace Poirot\View\Interpreter;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Interfaces\iLoaderAware;
use Poirot\Loader\Interfaces\iLoaderProvider;
use Poirot\Loader\PathStackLoader;
use Poirot\View\Exception\TemplateNotFoundException;
use Poirot\View\Interfaces\iInterpreterModel;
use Poirot\View\Interfaces\iPermutationViewModel;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\PermutationViewModel;

class PhpInterpret
    implements iInterpreterModel
    , iLoaderAware
    , iLoaderProvider
{
    /** @var PermutationViewModel */
    protected $_viewModel;
    /** @var iLoader */
    protected $resolver;
    /** @var IsoRenderer */
    protected $renderer;

    /** @var string Default Template File Extension */
    protected $templateExt = 'phtml';


    // Implement Interpreter:

    /**
     * Has Interpreter Support For This ViewModel?
     *
     * @param iViewModel $viewModel
     *
     * @return bool
     */
    function isAcceptable(iViewModel $viewModel)
    {
        return (
            $viewModel instanceof iPermutationViewModel
        );
    }

    /**
     * Set View Model
     *
     * - throw exception if ViewModel not supported
     *
     * @param iViewModel $viewModel
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setViewModel(iViewModel $viewModel)
    {
        if (!$this->isAcceptable($viewModel))
            throw new \InvalidArgumentException(sprintf(
                'ViewModel (%s) not Supported.'
                , get_class($viewModel)
            ));

        $this->_viewModel = $viewModel;

        return $this;
    }

    /**
     * Interpret Template From ViewModel
     *
     * - always get data from viewModel
     * - maybe template not supported by this interpreter
     *
     * @throws \Exception
     * @return string
     */
    function interpret()
    {
        $ext = $this->getFileExt();
        $__template = $this->_viewModel->getTemplate();
        $__tmp      = explode('.', $__template);

        if (!is_file($__template)) {
            ### We can set direct file path as template with extension included

            if ( end($__tmp) == $ext )
                throw new \Exception(sprintf(
                    'File Template Must Not Contain File Extension; for (%s).'
                    , $__template
                ));

            if (!is_file($__template.'.'.$ext))
                ## resolve to template file path from name
                $__template = $this->resolver()->resolve(
                    $__template
                    , function(&$resolved) use ($ext) {
                        if (file_exists($resolved.'.'.$ext)) {
                            ### return instance file path
                            $resolved .= '.'.$ext;
                            ### stop propagation and get resolved template file path
                            return true;
                        }
                    }
                );
        }

        ## Render Into Variable:
        $vars = $this->_viewModel->variables()->toArray();
        $renderer = $this->renderer();
        if ($renderer instanceof \Closure) {
            $renderer->bindTo($this);
            $__result = $renderer($__template, $vars);
        } else
            $__result = $renderer->capture($__template, $vars);

        return $__result;
    }

    // Implement PhpInterpret Specific

    /**
     * Set Template File Extension
     *
     * @param string $ext
     *
     * @return $this
     */
    function setFileExt($ext)
    {
        $this->templateExt = $ext;

        return $this;
    }

    /**
     * Get Template File Extension
     *
     * @return string
     */
    function getFileExt()
    {
        return $this->templateExt;
    }

    /**
     * Set Iso Renderer
     *
     * Closure Renderer:
     * - function($template, $vars)
     * - closure bind to Self Interpreter Object
     *
     * @param IsoRenderer|\Closure $renderer
     *
     * @return $this
     */
    function setRenderer($renderer)
    {
        if (!$renderer instanceof IsoRenderer && !$renderer instanceof \Closure)
            throw new \InvalidArgumentException(sprintf(
                'Renderer must extend of (IsoRenderer) or Closure function. given: (%s)'
                , \Poirot\Core\flatten($renderer)
            ));

        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get Iso Renderer
     *
     * @return IsoRenderer
     */
    function renderer()
    {
        if (!$this->renderer)
            $this->setRenderer(new IsoRenderer);

        return $this->renderer;
    }

    /**
     * Set Template Resolver
     *
     * @param iLoader $resolver
     *
     * @return $this
     */
    function setResolver(iLoader $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Template Resolver
     *
     * @return PathStackLoader|iLoader
     */
    function resolver()
    {
        if (!$this->resolver)
            $this->setResolver(new PathStackLoader);

        return $this->resolver;
    }
}
 