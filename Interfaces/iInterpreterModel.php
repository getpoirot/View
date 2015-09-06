<?php
namespace Poirot\View\Interfaces;

/*
 * We can also use Interpreter as Filter
 */
interface iInterpreterModel
{
    /**
     * Has Interpreter Support For This ViewModel?
     *
     * @param iViewModel $viewModel
     *
     * @return bool
     */
    function isAcceptable(iViewModel $viewModel);

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
    function setViewModel(iViewModel $viewModel);

    /**
     * Interpret Template From ViewModel
     *
     * - always get data from viewModel
     * - maybe template not supported by this interpreter
     *
     * @return string
     */
    function interpret();
}
