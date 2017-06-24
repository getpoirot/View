<?php
namespace Poirot\View;

use Poirot\Std\ErrorStack;
use Poirot\Std\Interfaces\Struct\iDataEntity;
use Poirot\Std\Struct\DataEntity;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\LoaderNamespaceStack;

use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\Interfaces\iViewRenderer;
use Poirot\View\ViewModel\RendererPhp;


class ViewModelTemplate
    extends aViewModel
    implements iViewModelPermutation
{
    /** @var iDataEntity */
    protected $variables;

    /** @var iLoader */
    protected $resolver;
    /** @var RendererPhp */
    protected $renderer;

    protected $template;


    /**
     * Render View Model
     * @return string
     * @throws \Exception
     */
    function render()
    {
        $Template = $this->getTemplate();
        if ($Template === null)
            // Nothing To Render!!
            return null;

        if (! is_file($Template) )
        {
            ## resolve to template file path from name
            $_t_template = $this->resolver()->resolve($Template);

            ## only if file resolved
            ($_t_template === false) ?: $Template = $_t_template;

            ##! Note:
            ##- if file not resolved let it handle within renderer
            ##- ..
        }

        ## Render Into Variable:
        $vars = \Poirot\Std\cast($this->variables())->toArray();
        $renderer = $this->renderer();

        // !! If Template not resolved also let renderer to resolve into template path.

        ErrorStack::handleError(); // handle errors --------------------\
        #
        if (is_callable($renderer))
            $result = call_user_func($renderer, $Template, $vars);
        else
            ### its renderer instance
            $result = $renderer->capture($Template, $vars);
        #
        if ($ex = ErrorStack::handleDone()) throw $ex; // --------------/


        return $result;
    }


    /**
     * Set Variables
     *
     * - Variables can include null value
     *
     * @param array|\Traversable $vars
     *
     * @return $this
     */
    function setVariables($vars)
    {
        $this->variables()->import($vars);
        return $this;
    }

    /**
     * Variables
     *
     * @return DataEntity
     */
    function variables()
    {
        if (! $this->variables )
            $this->variables = new DataEntity;

        return $this->variables;
    }


    // Options:

    /**
     * Set Iso Renderer
     *
     * Closure Renderer:
     * - function(string $templatePathName, array $viewVars)
     *
     * @param iViewRenderer|\Closure|callable $renderer
     *
     * @return $this
     */
    function setRenderer($renderer)
    {
        if (!$renderer instanceof iViewRenderer && !is_callable($renderer))
            throw new \InvalidArgumentException(sprintf(
                'Renderer must extend of (iViewRenderer) or callable. given: (%s)'
                , \Poirot\Std\flatten($renderer)
            ));

        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Get Iso Renderer
     *
     * @return RendererPhp|iViewRenderer|\Closure|callable $renderer
     */
    function renderer()
    {
        if (! $this->renderer )
            $this->setRenderer(new RendererPhp);

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
     * Proxy Helper Options For Resolver
     * @param $options
     */
    protected function setResolverOptions($options)
    {
        $resolver = $this->resolver();
        $resolver->with($resolver::parseWith($options));
    }

    /**
     * Template Resolver
     *
     * !! Needed Resource(Template) must resolved with
     *    Template Name and Template May exists within
     *    resolved section Or Resolved Template Returned
     *    Itself.
     *
     *    - resolve('Main') -> '/path/to/templates'[/Main.phtml]
     *                     This must check outside ------------
     *
     *    - resolve('Main') -> '/path/to/templates/Main.phtml'
     *
     * @return LoaderNamespaceStack|iLoader
     */
    function resolver()
    {
        if (!$this->resolver)
            $this->setResolver(new LoaderNamespaceStack);

        return $this->resolver;
    }

    /**
     * Set Template
     *
     * !! template file name (may without extension)
     *    it resolved by resolver object
     *
     * @param mixed $template
     *
     * @return $this
     */
    function setTemplate($template)
    {
        $this->template = (string) $template;
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
}
