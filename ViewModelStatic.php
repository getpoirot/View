<?php
namespace Poirot\View;


class ViewModelStatic 
    extends aViewModel
{
    /** @var string */
    protected $content;
    
    
    /**
     * Render View Model
     *
     * @return string
     * @throws \Exception
     * 
     * @see aViewModel::render
     */
    function doRender()
    {
        return $this->getContent();
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
    
    /**
     * Get Content
     *
     * @return string
     */
    function getContent()
    {
        return $this->content;
    }
}
