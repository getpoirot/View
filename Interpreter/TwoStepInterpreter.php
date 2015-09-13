<?php
namespace Poirot\View\Interpreter;

use Poirot\Loader\Interfaces\iLoaderProvider;
use Poirot\View\Interfaces\iInterpreterModel;
use Poirot\View\Interfaces\iPermutationViewModel;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\Interfaces\Respec\iMRendererProvider;
use Poirot\View\PermutationViewModel;

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

class TwoStepInterpreter implements iInterpreterModel
{
    /** @var PermutationViewModel */
    protected $_viewModel;

    /** @var iInterpreterModel */
    protected $baseInterpreter;
    /** @var PhpInterpret */
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
     * @param iInterpreterModel $interpreter
     * @return $this
     */
    function setBaseInterpreter(iInterpreterModel $interpreter)
    {
        $this->baseInterpreter = $interpreter;

        return $this;
    }

    /**
     * Get Base Interpreter
     *
     * @return iInterpreterModel
     */
    function getBaseInterpreter()
    {
        if (!$this->baseInterpreter)
            $this->baseInterpreter = new PhpInterpret;

        $interpreter = $this->baseInterpreter;
        $interpreter->setViewModel($this->_viewModel);

        return $interpreter;
    }

    /**
     * Get Layered Interpreter On Base
     *
     * @return PhpInterpret
     */
    function getLayeredInterpreter()
    {
        $interpreter = new PhpInterpret;

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
            /** $this PermutationViewModel */
            if (!file_exists($_l__template))
                ## the layered is optional and can be avoided.
                return;

            # render preLayer
            extract($__vars);
            unset($__vars);

            try {
                ob_start();
                include $_l__template;
                ob_get_clean();
            } catch (\Exception $e) {
                ob_end_clean();
                throw $e;
            }

            # set manipulated and new defined variables again
            $_l__baseViewModel->variables()->from(get_defined_vars());
        };

        if ($this->getBaseInterpreter() instanceof iMRendererProvider)
            ## Bind Layer Into Renderer
            $renderer = $renderer->bindTo($this->getBaseInterpreter()->renderer());
        else
            ## Bind Into Interpreter
            $renderer = $renderer->bindTo($this->getBaseInterpreter());

        return $renderer;
    }
}
 