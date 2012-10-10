# Kdyby/BootstrapRenderer [![Build Status](https://secure.travis-ci.org/Kdyby/Framework.png?branch=master)](http://travis-ci.org/Kdyby/Framework)

Forms Renderer for Nette Framework, that allows partial rendering and uses [Twitter Bootstrap markup and classes](http://twitter.github.com/bootstrap/base-css.html#forms).


## Requirements

Kdyby/BootstrapRenderer requires PHP 5.3.2 or higher.

- [Nette Framework 2.0.x](https://github.com/nette/nette)


## Installation

- [Get composer](http://getcomposer.org/doc/00-intro.md)
- Install package <code>kdyby/bootstrap-form-renderer</code>


## Macros

If you wanna use the special macros, you have to register them into Latte Engine

```php
Kdyby\Extension\Forms\BootstrapRenderer\Latte\FormMacros::install($engine->compiler);
```

Or simply register the extension in `app/bootstrap.php` to allow them globally

```php
Kdyby\Extension\Forms\BootstrapRenderer\DI\RendererExtension::register($configurator);
```


## Usage

First you have to register the renderer to form.

```php
use Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer;
$form->setRenderer(new BootstrapRenderer);
```

For performance optimizations, you can provider your own template instance.

```php
// $this instanceof Nette\Application\UI\Presenter
$form->setRenderer(new BootstrapRenderer($this->createTemplate()));
```

All the usage cases expects you to have the form component in variable named <code>$form</code>



### Basic rendering

Entire form

```smarty
{control formName} or {form formName /}
```

Beginning of the form

```smarty
{$form->render('begin')} or {form $form} or {form formName}
```

Errors

> Renders only errors, that have not associated form element.

```smarty
{$form->render('errors')} or {form errors}
```

Body

> Renders all controls and groups, that are not yet rendered.

```smarty
{$form->render('body')} or {form body}
```

Controls

> Renders all controls, that are not yet rendered. Doesn't render buttons.

```smarty
{$form->render('controls')} or {form controls}
```

Buttons

> Renders all buttons, that are not yet rendered.

```smarty
{$form->render('buttons')} or {form buttons}
```

End

> Renders all hidden inputs, and then the closing tag of form.

```smarty
{$form->render('end')} or {/form}
```


### Rendering of form components

Control

> Renders the container div around the control, its label and input.

```smarty
{$form->render($form['control-name'])} or {pair control-name}
```

Container

> Renders all the inputs in container, that are not yet rendered.

```smarty
{$form->render($form['container-name'])} or {container container-name}
```

Group

> Renders fieldset, legend and all the controls in group, that are not yet rendered.

```smarty
{$form->render($form->getGroup('Group name'))} or {group "Group name"}
```


-----

Kdyby Framework: homepage [http://www.kdyby.org](http://www.kdyby.org) and repository [http://github.com/kdyby/framework](http://github.com/kdyby/framework).
Sandbox, pre-packaged and configured project: [http://github.com/kdyby/sandbox](http://github.com/kdyby/sandbox)
