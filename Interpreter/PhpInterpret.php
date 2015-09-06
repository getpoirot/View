<?php
namespace Poirot\View\Interpreter;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\ResourceMapLoader;
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
        $this->__template = $this->_viewModel->getTemplate();
        if (!is_file($this->__template))
            $this->__template = $this->resolver($this->__template);

        if (!is_readable($this->__template))
            throw new \RuntimeException(sprintf(
                'Can`t Achieve Template File For (%s).'
                , $this->__template
            ));

        // TODO Isolated Embed Code to render file

        extract($this->_viewModel->variables()->toArray());

        try
        {
            ob_start();
            include $this->__template;
            $this->__result = ob_get_contents();
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
     * @return iLoader|ResourceMapLoader
     */
    function resolver()
    {
        if (!$this->resolver)
            $this->resolver = new ResourceMapLoader;

        return $this->resolver;
    }
}
 