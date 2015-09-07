<?php
namespace Poirot\View\Interpreter;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\PathStackLoader;
use Poirot\View\Interfaces\iInterpreterModel;
use Poirot\View\Interfaces\iPermutationViewModel;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\PermutationViewModel;

class PhpInterpret implements iInterpreterModel
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
        $ext = $this->getTemplateExt();
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

        if (!is_readable($__template))
            throw new \RuntimeException(sprintf(
                'Can`t Achieve Template File For (%s).'
                , implode('.', $__tmp)
            ));

        ## Render Into Variable:
        $vars = $this->_viewModel->variables()->toArray();
        $__result = $this->renderer()->capture($__template, $vars);

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
    function setTemplateExt($ext)
    {
        $this->templateExt = $ext;

        return $this;
    }

    /**
     * Get Template File Extension
     *
     * @return string
     */
    function getTemplateExt()
    {
        return $this->templateExt;
    }

    /**
     * Set Iso Renderer
     *
     * @param IsoRenderer $renderer
     *
     * @return $this
     */
    function setRenderer(IsoRenderer $renderer)
    {
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
     * @return iLoader|PathStackLoader
     */
    function resolver()
    {
        if (!$this->resolver)
            $this->setResolver(new PathStackLoader);

        return $this->resolver;
    }
}
 