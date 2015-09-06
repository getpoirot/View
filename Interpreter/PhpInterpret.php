<?php
namespace Poirot\View\Interpreter;

use Poirot\View\Interfaces\iInterpreterModel;
use Poirot\View\Interfaces\iPermutationViewModel;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\PermutationViewModel;

class PhpInterpret implements iInterpreterModel
{
    /** @var PermutationViewModel */
    protected $_viewModel;

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
     * @return string
     */
    function interpret()
    {
        $__template = $this->_viewModel->getTemplate();
        if (!is_file($__template))
            $__template = $this->resolver();

    }

    function setResolver($resolver)
    {

    }

    function resolver()
    {

    }
}
 