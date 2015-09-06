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

    // interpret tmp variables
    protected $__template;
    protected $__result;

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
        $this->__template = $this->_viewModel->getTemplate();
        $this->__tmp      = explode('.', $this->__template);
        if ( end($this->__tmp) == $ext )
            throw new \Exception(sprintf(
                'File Template Must Not Contain File Extension; for (%s).'
                , $this->__template
            ));

        if (!is_file($this->__template.'.'.$ext))
            ## resolve to template file path from name
            $this->__template = $this->resolver()->resolve(
                $this->__template
                , function(&$resolved) use ($ext) {
                    if (file_exists($resolved.'.'.$ext)) {
                        ### return instance file path
                        $resolved .= '.'.$ext;
                        ### stop propagation and get resolved template file path
                        return true;
                    }
                }
            );

        if (!is_readable($this->__template))
            throw new \RuntimeException(sprintf(
                'Can`t Achieve Template File For (%s).'
                , implode('.', $this->__tmp)
            ));


        // TODO Isolated Embed Code to render file

        extract($this->_viewModel->variables()->toArray());

        try
        {
            ob_start();
            include $this->__template;
            $this->__result = ob_get_clean();
        }
        catch (\Exception $e)
        {
            ob_end_clean();
            throw $e;
        }

        return $this->__result;
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
            $this->resolver = new PathStackLoader;

        return $this->resolver;
    }
}
 