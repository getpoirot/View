# View
Render Response Views

```
$view = new P\View\ViewModelTemplate([
    'resolver_options' => [
        '*' => __DIR__.'/templates'
    ],
]);
$bind = new P\View\ViewModelDecorateFeatures(clone $view);
$bind->setRenderer(\Poirot\View\renderManipulatedVars());
$bind->onNotifyRender = function($parentView, $self) {
    // Lookin for template_name.php beside base template
    /** @var P\View\ViewModelTemplate $self */
    $self->setExtension('php');
    $self->setTemplate($parentView->getTemplate());
    $self->setVariables($parentView->variables());
};
$bind->afterRender = function($result, $parent, $self) {
    if (is_array($result)) {
        /** @var P\View\ViewModelTemplate $parent */
        $parent->variables()->import($result);
    }
};

$view->bind($bind);

echo $view->setVariables(['user'=>'This is user'])->setTemplate('main')->render();
```