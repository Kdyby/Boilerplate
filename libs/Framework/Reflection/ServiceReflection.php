<?php

namespace Kdyby\Reflection;

use Nette;



/**
 * @author Filip Procházka
 *
 * @method Kdyby\Reflection\ServiceReflection from() from($class)
 */
class ServiceReflection extends Nette\Reflection\ClassReflection
{

    /**
     * @author Honza Marek
     *
     * @return array
     */
    public function getConstructorParamClasses()
    {
        $constructorReflection = $this->getConstructor();

        if ($constructorReflection === NULL) {
                return array();
        }

        $classes = array();
        foreach ($constructorReflection->getParameters() as $paramReflection) {
                $paramClass = $paramReflection->getClass();
                $classes[] = $paramClass ? $paramClass->getName() : NULL;
        }

        return $classes;
    }

}