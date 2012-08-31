# Kdyby/BootstrapRenderer [![Build Status](https://secure.travis-ci.org/Kdyby/Framework.png?branch=master)](http://travis-ci.org/Kdyby/Framework)

Forms Renderer for Nette Framework, that allows partial rendering and uses [Twitter Bootstrap markup and classes](http://twitter.github.com/bootstrap/base-css.html#forms).


## Requirements

Kdyby/BootstrapRenderer requires PHP 5.3.2 or higher.

- [Nette Framework 2.0.x](https://github.com/nette/nette)


## Installation

- [Get composer](http://getcomposer.org/doc/00-intro.md)
- Install package <code>kdyby/bootstrap-form-renderer</code>


## Usage

First you have to register the renderer to form.

    use Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer;
    $form->setRenderer(new BootstrapRenderer);

For performance optimizations, you can provider your own template instance.

    // $this instanceof Nette\Application\UI\Presenter
    $form->setRenderer(new BootstrapRenderer($this->createTemplate()));

All the usage cases expects you to have the form component in variable named <code>$form</code>


### Basic rendering

Entire form

    {control formName}

Beginning of the form

    {$form->render('begin')}

Errors

> Renders only errors, that have not associated form element.

    {$form->render('errors')}

Body

> Renders all controls, that are not yet rendered.

    {$form->render('body')}

End

> Renders all hidden inputs, and then the closing tag of form.

    {$form->render('end')}


### Rendering of form components

Control

> Renders the container div around the control, its label and input.

    {$form->render($form['name'])}

Container

> Renders all the inputs in container, that are not yet rendered.

    {$form->render($form['name'])}

Group

> Renders fieldset, legend and all the controls in group, that are not yet rendered.

    {$form->render($form['name'])}



-----

Kdyby Framework: homepage [http://www.kdyby.org](http://www.kdyby.org) and repository [http://github.com/kdyby/framework](http://github.com/kdyby/framework).
Sandbox, pre-packaged and configured project: [http://github.com/kdyby/sandbox](http://github.com/kdyby/sandbox)
