<?php
namespace Poirot\View;

use Poirot\View\ViewModel\RendererPhp;


/*
$viewTemplate = new ViewModelTemplate;
$viewTemplate
    ->setRenderer( new RendererPhp )
    ->setTemplate('template.phtml')
    ->resolver(function($resolver) {
        $resolver->addResource('**', __DIR__.'/data/');
    })
    ->setVariables([
        'content' => 'default_content',
        'footer'  => 'this is footer',
    ])
;

$viewTemplateBindable = FactoryView::makeTwoStepView($viewTemplate);

echo $viewTemplateBindable->render();
*/

class ViewFactory
{
    /**
     * Make Given ViewModelTemplate To Two Step View Renderer
     *
     * @param ViewModelTemplate $viewModel
     *
     * @return DecorateViewModel
     * @throws \Exception
     */
    static function makeTwoStepView(ViewModelTemplate $viewModel)
    {
        $viewTemplateBindable = new DecorateViewModel($viewModel);
        self::bindTwoStepViewToDecorator($viewTemplateBindable);
        return $viewTemplateBindable;
    }

    /**
     * Bind Two Step View Renderer To Given ViewModelTemplate
     *
     * @param DecorateViewModel $viewModel
     *
     * @throws \Exception
     */
    static function bindTwoStepViewToDecorator(DecorateViewModel $viewModel)
    {
        $twoStepDecorator = new DecorateViewModel(new ViewModelTemplate);
        $twoStepDecorator->setRenderer(function($template, $vars)
        {
            $renderer = new RendererPhp;

            $result   = $renderer->capture($template, $vars);
            return $result;
        });

        $twoStepDecorator->onBeforeViewModelRender(function ($parent, $self) {
            // get full uri address of template
            $template = $parent->resolver()->resolve( $parent->getTemplate() );
            if ( $template ) {
                // change main template name extension to .php extension
                $template = substr_replace($template , 'php', strrpos($template , '.') +1);
                if (! file_exists($template) )
                    // two step-view render is optional;
                    // if not exists return false to notify decorator to not render view
                    return false;

                $self->setTemplate($template);
            }

            $self->setVariables( $parent->variables() );
        });

        $twoStepDecorator->onAfterViewModelRendered(function ($result, $parent, $self) {
            if ($result === null)
                ## Nothing to do!!
                return;

            if (! is_callable($result) )
                throw new \Exception(sprintf(
                    'Result return from (%s) template as step layer is not valid callable; given (%s).'
                    , $self->getTemplate(), \Poirot\Std\flatten($result)
                ));


            call_user_func($result, $parent, $self);
        });


        $viewModel->bind($twoStepDecorator, PHP_INT_MAX, 'two_step_view');
    }
}
