<?php
namespace Poirot\View\Interpreter;

use Poirot\Loader\Interfaces\iLoaderProvider;
use Poirot\View\Interfaces\iInterpreterView;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\Interfaces\Respec\iRendererProvider;
use Poirot\View\ViewModelTemplate;

/*
$viewModel    = new PermutationViewModel;

$twoStepInter = new TwoStepInterpreter;
$baseLayer    = (new PhpInterpret);
$baseLayer->resolver()->setStack('*', APP_DIR_THEME_DEFAULT);
$twoStepInter->setBaseInterpreter($baseLayer);

$viewModel->setInterpreter($twoStepInter);
$viewModel->setVariables(['content' => '<h1>Hello World!</h1>']);
$viewModel->setTemplate('default');

echo $viewModel->render();
*/

class TwoStepInterpreter implements iInterpreterView
{
    /** @var ViewModelTemplate */
    protected $_viewModel;

    /** @var iInterpreterView */
    protected $baseInterpreter;
    /** @var InterpreterPhpView */
    protected $layeredInterpreter;

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
            $viewModel instanceof iViewModelPermutation
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
        // Interpret and manipulate viewModel
        $layer = $this->getLayeredInterpreter();
        $layer->interpret();

        $base  = $this->getBaseInterpreter();
        return $base->interpret();
    }


    // Implement TwoStep View

    /**
     * Set Base Interpreter
     *
     * - Inject View Model
     *
     * @param iInterpreterView $interpreter
     * @return $this
     */
    function setBaseInterpreter(iInterpreterView $interpreter)
    {
        $this->baseInterpreter = $interpreter;

        return $this;
    }

    /**
     * Get Base Interpreter
     *
     * @return iInterpreterView
     */
    function getBaseInterpreter()
    {
        if (!$this->baseInterpreter)
            $this->baseInterpreter = new InterpreterPhpView;

        $interpreter = $this->baseInterpreter;
        $interpreter->setViewModel($this->_viewModel);

        return $interpreter;
    }

    /**
     * Get Layered Interpreter On Base
     *
     * @return InterpreterPhpView
     */
    function getLayeredInterpreter()
    {
        $interpreter = new InterpreterPhpView;

        # base layer setup
        $interpreter->setFileExt('php');
        $interpreter->setRenderer($this->__getLayerRenderer());

        ## same template resolver as base
        if (($baseInterpreter = $this->getBaseInterpreter()) instanceof iLoaderProvider)
            $interpreter->setResolver($baseInterpreter->resolver());

        $this->layeredInterpreter = $interpreter;

        $this->layeredInterpreter->setViewModel($this->_viewModel);

        return $this->layeredInterpreter;
    }

    /**
     * Forward Proxy call to BaseInterpreter
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    function __call($method, $args)
    {
        return call_user_func_array([$this->getBaseInterpreter(), $method], $args);
    }


    /**
     * Layer Renderer is a closure that bind to interpreter-
     * or renderer of interpreter if implemented.
     *
     * - all new defined or manipulated variables inside layer stage-
     *   will replaced with variables of base view model.
     * - also cause of bind we have access to all interpreter or renderer-
     *   methods and variables.
     *
     * @return callable
     */
    protected function __getLayerRenderer()
    {
        // Layer just manipulate ViewModel by Binding to Object
        $_l__baseViewModel = $this->_viewModel;
        $renderer  = function($_l__template, $__vars) use ($_l__baseViewModel)
        {
            ## hide variables from scope
            $this->_l__template = $_l__template;
            unset($_l__template);

            # base view model may change if we render another template inside include script
//            $this->_l__baseViewModel = $_l__baseViewModel;
//            unset($_l__baseViewModel);


            /** $this PermutationViewModel */
            if (!file_exists($this->_l__template))
                ## the layered is optional and can be avoided.
                return;

            # render preLayer
            extract($__vars);
            unset($__vars);

            try {
                ob_start();
                include $this->_l__template;
                ob_get_clean();
            } catch (\Exception $e) {
                ob_end_clean();
                throw $e;
            }

            # set manipulated and new defined variables again
            $vars = get_defined_vars();
            unset($vars['_l__baseViewModel']);
            $_l__baseViewModel->variables()->from($vars);

//            unset($this->_l__baseViewModel);
            unset($this->_l__template);
        };

        if ($this->getBaseInterpreter() instanceof iRendererProvider)
            ## Bind Layer Into Renderer
            $renderer = $renderer->bindTo($this->getBaseInterpreter()->renderer());
        else
            ## Bind Into Interpreter
            $renderer = $renderer->bindTo($this->getBaseInterpreter());

        return $renderer;
    }
}
 