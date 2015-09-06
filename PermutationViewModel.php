<?php
namespace Poirot\View;

use Poirot\Core\Entity;
use Poirot\View\Interfaces\iInterpreterModel;
use Poirot\View\Interfaces\iPermutationViewModel;
use Poirot\View\Interfaces\Respec\iMInterpreterAware;
use Poirot\View\Interfaces\Respec\iMInterpreterProvider;
use Poirot\View\Interfaces\Respec\iMTemplateAware;
use Poirot\View\Interfaces\Respec\iMTemplateProvider;
use Poirot\View\Interpreter\PhpInterpret;

class PermutationViewModel extends AbstractViewModel
    implements iPermutationViewModel
    , iMInterpreterAware
    , iMInterpreterProvider
    , iMTemplateAware
    , iMTemplateProvider
{
    /** @var Entity */
    protected $variables;

    /** @var iInterpreterModel */
    protected $interpreter;
    protected $template;

    /**
     * Render View Model
     *
     * - render bind view models first
     *
     * @return string
     */
    function render()
    {
        parent::render();

        return $this->interpreter()->interpret();
    }

    /**
     * Set Variables
     *
     * @param array|Entity $vars
     *
     * @return $this
     */
    function setVariables($vars)
    {
        $this->variables()->from($vars);

        return $this;
    }

    /**
     * Variables
     *
     * @return Entity
     */
    function variables()
    {
        if (!$this->variables)
            $this->variables = new Entity;

        return $this->variables;
    }

    /**
     * Set Template
     *
     * - template must supported with engine
     *
     * @param mixed $template
     *
     * @return $this
     */
    function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get Template
     *
     * @return mixed
     */
    function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set Interpreter Engine
     *
     * @param iInterpreterModel $engine
     *
     * @return $this
     */
    function setInterpreter(iInterpreterModel $engine)
    {
        $this->interpreter = $engine;

        return $this;
    }

    /**
     * Get Interpreter Engine
     *
     * @return iInterpreterModel
     */
    function interpreter()
    {
        if (!$this->interpreter)
            $this->interpreter = new PhpInterpret;

        $this->interpreter->setViewModel($this);

        return $this->interpreter;
    }
}
 