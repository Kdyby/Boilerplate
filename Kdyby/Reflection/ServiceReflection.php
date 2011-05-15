<?php

namespace Kdyby\Reflection;

use Nette;



/**
 * @author Filip Procházka
 *
 * @method Kdyby\Reflection\ServiceReflection from() from($class)
 */
class ServiceReflection extends Nette\Reflection\ClassType
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

        $args = array();
        foreach ($constructorReflection->getParameters() as $paramReflection) {
				if ($paramReflection->isDefaultValueAvailable()) {
					$args[] = $paramReflection->getDefaultValue();

				} elseif ($paramReflection->getClass()) {
					$args[] = $container->getServiceByType($paramReflection->getClass()->getName());

				} else {
					$args[] = $param->isArray() && !$param->allowsNull() ? array() : NULL;
				}
        }

        return $args;
    }

}