<?php
namespace Poirot\View;

class TextViewModel extends AbstractViewModel
{
    /** @var string */
    protected $content;

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

        return $this->getContent();
    }

    /**
     * Get Content
     *
     * @return string
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * Set Content
     *
     * @param string $content
     *
     * @return $this
     */
    function setContent($content)
    {
        $this->content = (string) $content;

        return $this;
    }
}
